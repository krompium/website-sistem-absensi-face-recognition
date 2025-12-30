<div x-data="framesCarousel('{{ $session_id }}', {{ $frames_count }})" x-init="loadFrames()" class="frames-carousel-container">
    <!-- Loading State -->
    <div x-show="loading" class="flex flex-col justify-center items-center py-16">
        <svg class="animate-spin h-12 w-12 text-primary-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <p class="text-gray-600 dark:text-gray-300 text-lg">Loading frames...</p>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-2"
            x-text="'Loading ' + loadedCount + ' of ' + totalFrames"></p>
    </div>

    <!-- Main Carousel -->
    <div x-show="!loading" class="space-y-6">
        <!-- Large Image Display -->
        <div class="relative bg-black rounded-lg overflow-hidden" style="min-height: 500px;">
            <!-- Navigation Arrows -->
            <button @click="previousFrame()" x-show="currentIndex > 0"
                class="absolute left-4 top-1/2 -translate-y-1/2 z-10 bg-black/70 hover:bg-black/90 text-white p-4 rounded-full transition-all duration-200 hover:scale-110">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Current Frame Image -->
            <div class="flex items-center justify-center" style="min-height: 500px;">
                <img : src="frames[currentIndex]?.url" :alt="'Frame ' + frames[currentIndex]?.number"
                    class="max-h-[500px] max-w-full object-contain" @load="imageLoaded()">
            </div>

            <button @click="nextFrame()" x-show="currentIndex < frames.length - 1"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-10 bg-black/70 hover:bg-black/90 text-white p-4 rounded-full transition-all duration-200 hover:scale-110">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Frame Counter Overlay -->
            <div
                class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/80 text-white px-6 py-3 rounded-full text-lg font-semibold">
                Frame <span x-text="currentIndex + 1"></span> / <span x-text="frames.length"></span>
            </div>

            <!-- Play/Pause Button -->
            <div class="absolute top-4 right-4">
                <button @click="toggleAutoplay()"
                    class="bg-black/70 hover:bg-black/90 text-white p-3 rounded-full transition-all">
                    <svg x-show="! isPlaying" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                    <svg x-show="isPlaying" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Slider Range -->
        <div class="space-y-2">
            <input type="range" : min="0" : max="frames.length - 1" x-model. number="currentIndex"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-primary-600"
                style="height: 8px;">

            <!-- Frame Info -->
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>Frame #<span x-text="frames[currentIndex]?.number"></span></span>
                <span x-text="Math.round((currentIndex / (frames.length - 1)) * 100) + '%'"></span>
            </div>
        </div>

        <!-- Thumbnail Strip -->
        <div class="relative">
            <div class="overflow-x-auto pb-4">
                <div class="flex gap-2 min-w-max px-2">
                    <template x-for="(frame, index) in frames" :key="frame.number">
                        <div @click="currentIndex = index" :
                            class="{
                                'ring-4 ring-primary-500 scale-105': currentIndex === index,
                                'opacity-60 hover:opacity-100': currentIndex !== index
                            }"
                            class="cursor-pointer transition-all duration-200 flex-shrink-0">
                            <img :src="frame.url" :alt="'Frame ' + frame.number"
                                class="w-24 h-24 object-cover rounded-lg shadow-md" loading="lazy">
                            <p class="text-xs text-center mt-1 text-gray-600 dark:text-gray-400"
                                x-text="'#' + frame.number"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Playback Controls -->
        <div class="flex items-center justify-center gap-4 py-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <!-- Speed Control -->
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600 dark:text-gray-400">Speed: </label>
                <select x-model. number="playbackSpeed"
                    class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                    <option value="2000">0.5x</option>
                    <option value="1000">1x</option>
                    <option value="500">2x</option>
                    <option value="250">4x</option>
                    <option value="100">10x</option>
                </select>
            </div>

            <!-- Control Buttons -->
            <div class="flex gap-2">
                <button @click="currentIndex = 0"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded transition">
                    ⏮ First
                </button>

                <button @click="toggleAutoplay()"
                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded transition font-semibold">
                    <span x-show="!isPlaying">▶ Play</span>
                    <span x-show="isPlaying">⏸ Pause</span>
                </button>

                <button @click="currentIndex = frames.length - 1"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover: bg-gray-600 rounded transition">
                    Last ⏭
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function framesCarousel(sessionId, totalFrames) {
        return {
            sessionId: sessionId,
            totalFrames: totalFrames,
            frames: [],
            currentIndex: 0,
            loading: true,
            loadedCount: 0,
            isPlaying: false,
            playbackSpeed: 1000, // milliseconds
            playInterval: null,

            async loadFrames() {
                this.loading = true;
                this.frames = [];

                // Generate frame list
                for (let i = 1; i <= this.totalFrames; i++) {
                    const frameNum = String(i).padStart(4, '0');
                    this.frames.push({
                        number: frameNum,
                        url: `/secure-image/frame/${this.sessionId}/${frameNum}`
                    });
                }

                // Preload first image
                const firstImg = new Image();
                firstImg.onload = () => {
                    this.loading = false;
                };
                firstImg.src = this.frames[0].url;
            },

            nextFrame() {
                if (this.currentIndex < this.frames.length - 1) {
                    this.currentIndex++;
                } else if (this.isPlaying) {
                    // Loop back to start
                    this.currentIndex = 0;
                }
            },

            previousFrame() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                }
            },

            toggleAutoplay() {
                this.isPlaying = !this.isPlaying;

                if (this.isPlaying) {
                    this.playInterval = setInterval(() => {
                        this.nextFrame();
                    }, this.playbackSpeed);
                } else {
                    if (this.playInterval) {
                        clearInterval(this.playInterval);
                        this.playInterval = null;
                    }
                }
            },

            imageLoaded() {
                this.loadedCount++;
            },

            // Watch playback speed changes
            $watch: {
                playbackSpeed(newSpeed) {
                    if (this.isPlaying) {
                        clearInterval(this.playInterval);
                        this.playInterval = setInterval(() => {
                            this.nextFrame();
                        }, newSpeed);
                    }
                }
            }
        }
    }
</script>

<style>
    .frames-carousel-container {
        min-height: 600px;
    }

    /* Custom scrollbar for thumbnail strip */
    .overflow-x-auto: :-webkit-scrollbar {
        height: 8px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Dark mode scrollbar */
    . dark .overflow-x-auto::-webkit-scrollbar-track {
        background: #374151;
    }

    .dark . overflow-x-auto::-webkit-scrollbar-thumb {
        background: #6b7280;
    }

    .dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>
