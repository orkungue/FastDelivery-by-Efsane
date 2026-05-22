<div
    x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        drawing: false,
        canvas: null,
        context: null,

        init() {
            this.canvas = this.$refs.canvas;
            this.context = this.canvas.getContext('2d');

            this.resizeCanvas();

            if (this.state) {
                this.drawSavedImage(this.state);
            }

            window.addEventListener('resize', () => {
                const currentState = this.state;

                this.resizeCanvas();

                if (currentState) {
                    this.drawSavedImage(currentState);
                }
            });
        },

        resizeCanvas() {
            const ratio = window.devicePixelRatio || 1;
            const width = this.canvas.offsetWidth;
            const height = this.canvas.offsetHeight;

            this.canvas.width = width * ratio;
            this.canvas.height = height * ratio;

            this.context.setTransform(ratio, 0, 0, ratio, 0, 0);
            this.context.lineWidth = 2.5;
            this.context.lineCap = 'round';
            this.context.lineJoin = 'round';
            this.context.strokeStyle = '#111';
        },

        drawSavedImage(src) {
            const image = new Image();

            image.onload = () => {
                const canvasWidth = this.canvas.offsetWidth;
                const canvasHeight = this.canvas.offsetHeight;

                this.context.clearRect(0, 0, canvasWidth, canvasHeight);

                const scale = Math.min(
                    canvasWidth / image.width,
                    canvasHeight / image.height
                );

                const width = image.width * scale;
                const height = image.height * scale;

                const x = (canvasWidth - width) / 2;
                const y = (canvasHeight - height) / 2;

                this.context.drawImage(image, x, y, width, height);
            };

            image.src = src;
        },

        position(event) {
            const rect = this.canvas.getBoundingClientRect();
            const point = event.touches ? event.touches[0] : event;

            return {
                x: point.clientX - rect.left,
                y: point.clientY - rect.top,
            };
        },

        start(event) {
            event.preventDefault();

            this.drawing = true;

            const pos = this.position(event);

            this.context.beginPath();
            this.context.moveTo(pos.x, pos.y);
        },

        draw(event) {
            if (! this.drawing) {
                return;
            }

            event.preventDefault();

            const pos = this.position(event);

            this.context.lineTo(pos.x, pos.y);
            this.context.stroke();
        },

        stop() {
            if (! this.drawing) {
                return;
            }

            this.drawing = false;
            this.state = this.canvas.toDataURL('image/png');
        },

        clear() {
            this.context.clearRect(0, 0, this.canvas.offsetWidth, this.canvas.offsetHeight);
            this.state = null;
        },
    }"
    class="space-y-2"
>
    <div class="overflow-hidden rounded-xl border border-gray-300 bg-white">
        <canvas
            x-ref="canvas"
            class="block w-full cursor-crosshair"
            style="height: 220px; touch-action: none;"
            @mousedown="start($event)"
            @mousemove="draw($event)"
            @mouseup="stop()"
            @mouseleave="stop()"
            @touchstart="start($event)"
            @touchmove="draw($event)"
            @touchend="stop()"
        ></canvas>
    </div>

    <button
        type="button"
        x-on:click="clear()"
        class="text-sm font-medium text-red-600 hover:underline"
    >
        Unterschrift löschen
    </button>
</div>