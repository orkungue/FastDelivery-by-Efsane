<div
    x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        drawing: false,
        canvas: null,
        context: null,

        init() {
            this.canvas = this.$refs.canvas;
            this.context = this.canvas.getContext('2d');

            this.resize();

            if (this.state) {
                const image = new Image();
                image.onload = () => this.context.drawImage(image, 0, 0, this.canvas.width, this.canvas.height);
                image.src = this.state;
            }
        },

        resize() {
            this.canvas.width = this.canvas.offsetWidth;
            this.canvas.height = 180;

            this.context.lineWidth = 2;
            this.context.lineCap = 'round';
            this.context.strokeStyle = '#111';
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
            if (! this.drawing) return;

            event.preventDefault();

            const pos = this.position(event);
            this.context.lineTo(pos.x, pos.y);
            this.context.stroke();

            this.state = this.canvas.toDataURL('image/png');
        },

        stop() {
            this.drawing = false;
            this.state = this.canvas.toDataURL('image/png');
        },

        clear() {
            this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.state = null;
        },
    }"
    class="space-y-2"
>
    <canvas
        x-ref="canvas"
        class="w-full rounded-lg border border-gray-300 bg-white"
        style="height: 180px; touch-action: none;"
        @mousedown="start($event)"
        @mousemove="draw($event)"
        @mouseup="stop()"
        @mouseleave="stop()"
        @touchstart="start($event)"
        @touchmove="draw($event)"
        @touchend="stop()"
    ></canvas>

    <button type="button" x-on:click="clear()" class="text-sm text-red-600">
        Unterschrift löschen
    </button>
</div>