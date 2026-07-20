<template>
  <article ref="root" class="card p-4 rounded-lg bg-white/3 backdrop-blur-sm transform transition-transform">
    <h3 class="text-lg font-semibold">Panel {{ index }}</h3>
    <p class="text-sm text-gray-300 mt-2">Summary and quick actions.</p>
    <div class="mt-4">
      <a href="#" class="inline-block px-3 py-1 rounded bg-blue-500 text-white text-sm">Open</a>
    </div>
  </article>
</template>

<script lang="ts">
import { defineComponent, onMounted, onBeforeUnmount, ref } from 'vue'

export default defineComponent({
  name: 'Card3D',
  props: { index: { type: Number, required: true } },
  setup() {
    const root = ref<HTMLElement | null>(null)

    function handleMouseMove(e: MouseEvent) {
      if (!root.value) return
      const r = root.value.getBoundingClientRect()
      const px = (e.clientX - r.left) / r.width - 0.5
      const py = (e.clientY - r.top) / r.height - 0.5
      const rx = -py * 8
      const ry = px * 8
      root.value.style.transform = `perspective(900px) rotateX(${rx}deg) rotateY(${ry}deg) translateZ(6px)`
    }

    function reset() {
      if (!root.value) return
      root.value.style.transform = ''
    }

    onMounted(() => {
      window.addEventListener('mousemove', handleMouseMove)
      window.addEventListener('mouseleave', reset)
    })

    onBeforeUnmount(() => {
      window.removeEventListener('mousemove', handleMouseMove)
      window.removeEventListener('mouseleave', reset)
    })

    return { root }
  }
})
</script>

<style scoped>
.card{transition:transform .18s ease, box-shadow .18s ease; box-shadow: 0 10px 30px rgba(2,6,23,0.4)}
.card:hover{box-shadow: 0 30px 60px rgba(2,6,23,0.6)}
</style>
