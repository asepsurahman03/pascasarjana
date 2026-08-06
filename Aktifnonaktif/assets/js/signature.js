/**
 * Signature Canvas Manager
 * Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
 * Standalone canvas-based digital signature library
 */
class SignatureManager {
    constructor(canvasId, options = {}) {
        this.canvas  = document.getElementById(canvasId);
        if (!this.canvas) {
            console.warn('[SignatureManager] Canvas not found:', canvasId);
            return;
        }

        this.ctx       = this.canvas.getContext('2d');
        this.isDrawing = false;
        this.lastX     = 0;
        this.lastY     = 0;
        this.isEmpty   = true;
        this.history   = []; // For undo
        this.maxHistory = 20;

        // Options
        this.options = {
            lineWidth:   options.lineWidth   || 2.5,
            strokeColor: options.strokeColor || '#1a1a2e',
            background:  options.background  || '#ffffff',
            smoothing:   options.smoothing   || true,
            ...options
        };

        this.callbacks = {
            onChange: options.onChange || null,
            onClear:  options.onClear  || null,
        };

        this._init();
    }

    _init() {
        // Set canvas resolution for high-DPI displays
        this._resizeCanvas();

        // Configure context
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth   = this.options.lineWidth;
        this.ctx.lineCap     = 'round';
        this.ctx.lineJoin    = 'round';

        // Mouse events
        this.canvas.addEventListener('mousedown',  this._onMouseDown.bind(this));
        this.canvas.addEventListener('mousemove',  this._onMouseMove.bind(this));
        this.canvas.addEventListener('mouseup',    this._onMouseUp.bind(this));
        this.canvas.addEventListener('mouseleave', this._onMouseUp.bind(this));

        // Touch events (passive: false prevents scroll during signing)
        this.canvas.addEventListener('touchstart', e => {
            e.preventDefault();
            this._onMouseDown(e.touches[0]);
        }, { passive: false });

        this.canvas.addEventListener('touchmove', e => {
            e.preventDefault();
            this._onMouseMove(e.touches[0]);
        }, { passive: false });

        this.canvas.addEventListener('touchend', this._onMouseUp.bind(this));

        // Resize observer for responsive canvas
        if (window.ResizeObserver) {
            const ro = new ResizeObserver(() => this._resizeCanvas());
            ro.observe(this.canvas.parentElement || this.canvas);
        } else {
            window.addEventListener('resize', () => this._resizeCanvas());
        }
    }

    _resizeCanvas() {
        const rect = this.canvas.getBoundingClientRect();
        const dpr  = window.devicePixelRatio || 1;

        // Save current content
        let imgData = null;
        if (!this.isEmpty && this.canvas.width > 0) {
            try {
                imgData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
            } catch(e) { /* cross-origin */ }
        }

        this.canvas.width  = rect.width  * dpr;
        this.canvas.height = rect.height * dpr;
        this.ctx.scale(dpr, dpr);

        // Restore styling after resize
        this.ctx.strokeStyle = this.options.strokeColor;
        this.ctx.lineWidth   = this.options.lineWidth;
        this.ctx.lineCap     = 'round';
        this.ctx.lineJoin    = 'round';

        // Restore content
        if (imgData) {
            this.ctx.putImageData(imgData, 0, 0);
        }
    }

    _getPos(e) {
        const rect = this.canvas.getBoundingClientRect();
        const x = (e.clientX !== undefined ? e.clientX : e.pageX) - rect.left;
        const y = (e.clientY !== undefined ? e.clientY : e.pageY) - rect.top;
        return { x, y };
    }

    _onMouseDown(e) {
        this.isDrawing = true;
        const pos = this._getPos(e);
        this.lastX = pos.x;
        this.lastY = pos.y;

        // Save state for undo
        this._saveHistory();

        // Start a new path at the click point
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x, pos.y);
    }

    _onMouseMove(e) {
        if (!this.isDrawing) return;
        const pos = this._getPos(e);

        if (this.options.smoothing) {
            // Smooth line using quadratic bezier midpoints
            const midX = (this.lastX + pos.x) / 2;
            const midY = (this.lastY + pos.y) / 2;
            this.ctx.quadraticCurveTo(this.lastX, this.lastY, midX, midY);
        } else {
            this.ctx.lineTo(pos.x, pos.y);
        }

        this.ctx.stroke();
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x, pos.y);

        this.lastX = pos.x;
        this.lastY = pos.y;

        this.isEmpty = false;

        if (this.callbacks.onChange) {
            this.callbacks.onChange(this.toDataURL());
        }
    }

    _onMouseUp() {
        if (!this.isDrawing) return;
        this.isDrawing = false;
        this.ctx.beginPath();
    }

    _saveHistory() {
        if (this.history.length >= this.maxHistory) {
            this.history.shift(); // Remove oldest
        }
        this.history.push(
            this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height)
        );
    }

    /**
     * Clear the canvas
     */
    clear() {
        const rect = this.canvas.getBoundingClientRect();
        this.ctx.clearRect(0, 0, rect.width * (window.devicePixelRatio || 1), rect.height * (window.devicePixelRatio || 1));
        this.isEmpty  = true;
        this.history  = [];
        if (this.callbacks.onChange) this.callbacks.onChange('');
        if (this.callbacks.onClear) this.callbacks.onClear();
    }

    /**
     * Undo last stroke
     */
    undo() {
        if (this.history.length === 0) return;
        const prev = this.history.pop();
        this.ctx.putImageData(prev, 0, 0);
        this.isEmpty = this.history.length === 0;
        if (this.callbacks.onChange) {
            this.callbacks.onChange(this.isEmpty ? '' : this.toDataURL());
        }
    }

    /**
     * Export as base64 PNG data URL
     */
    toDataURL(type = 'image/png', quality = 0.92) {
        if (this.isEmpty) return '';
        return this.canvas.toDataURL(type, quality);
    }

    /**
     * Load a base64 image into the canvas
     */
    fromDataURL(dataUrl) {
        if (!dataUrl) return;
        const img = new Image();
        img.onload = () => {
            const rect = this.canvas.getBoundingClientRect();
            this.ctx.drawImage(img, 0, 0, rect.width, rect.height);
            this.isEmpty = false;
        };
        img.src = dataUrl;
    }

    /**
     * Check if signature area is large enough (anti-trivial-sig)
     */
    isValid(minPx = 500) {
        if (this.isEmpty) return false;
        const data   = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data;
        let nonEmpty = 0;
        for (let i = 3; i < data.length; i += 4) {
            if (data[i] > 0) nonEmpty++;
        }
        return nonEmpty > minPx;
    }

    /**
     * Get percentage of canvas filled
     */
    getFilledPercent() {
        const data   = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data;
        const total  = data.length / 4;
        let filled   = 0;
        for (let i = 3; i < data.length; i += 4) {
            if (data[i] > 0) filled++;
        }
        return Math.round((filled / total) * 100);
    }

    /**
     * Destroy (remove event listeners)
     */
    destroy() {
        this.canvas.removeEventListener('mousedown',  this._onMouseDown);
        this.canvas.removeEventListener('mousemove',  this._onMouseMove);
        this.canvas.removeEventListener('mouseup',    this._onMouseUp);
        this.canvas.removeEventListener('mouseleave', this._onMouseUp);
        this.canvas.removeEventListener('touchstart', this._onMouseDown);
        this.canvas.removeEventListener('touchmove',  this._onMouseMove);
        this.canvas.removeEventListener('touchend',   this._onMouseUp);
    }
}

// Export for use in forms
window.SignatureManager = SignatureManager;
