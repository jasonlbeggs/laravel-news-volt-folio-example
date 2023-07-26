<div
    x-data="{
        activeEpisode: null,
        play(episode) {
            this.activeEpisode = episode

            this.$nextTick(() => {
                this.$refs.audio.play()
            })
        },
    }"
    x-show="activeEpisode"
    x-transition.opacity.duration.500ms
    x-on:play-episode.window="play($event.detail)"
    class="fixed inset-x-0 bottom-0 w-full border-t border-gray-200 bg-white"
    style="display: none"
>
    <div class="mx-auto max-w-xl p-6">
        <h3
            x-text="`Playing: No. ${activeEpisode?.number} - ${activeEpisode?.title}`"
            class="text-center text-sm font-medium text-gray-600"
        ></h3>
        <audio
            x-ref="audio"
            class="mx-auto mt-3"
            :src="activeEpisode?.audio"
            controls
        ></audio>
    </div>
</div>
