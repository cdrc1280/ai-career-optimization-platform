<template>
  <!-- Floating Action Button -->
  <button 
    @click="copilot.toggleDrawer()"
    class="fixed bottom-6 right-6 z-[60] w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30 hover:scale-105 transition-transform group"
    :class="{ 'rotate-12': copilot.isDrawerOpen }"
  >
    <svg v-if="!copilot.isDrawerOpen" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
    </svg>
    <svg v-else class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
    </svg>
    <span v-if="!copilot.isDrawerOpen && copilot.activeMessages.length && !copilot.isDrawerOpen" class="absolute top-0 right-0 w-3 h-3 bg-red-500 border-2 border-slate-900 rounded-full"></span>
  </button>

  <!-- Floating Chat Window -->
  <Transition name="chat-window">
    <div v-if="copilot.isDrawerOpen" class="fixed bottom-24 right-6 z-[60] w-[360px] h-[600px] max-h-[calc(100vh-120px)] bg-bg-surface border border-border shadow-2xl rounded-2xl flex flex-col glass-panel overflow-hidden">
      <!-- Header -->
      <div class="px-5 py-4 border-b border-border flex items-center justify-between bg-bg-card/50 backdrop-blur-sm">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <h3 class="font-bold text-text-primary leading-tight">CareerAI Copilot</h3>
            <p class="text-[11px] text-blue-400 font-medium">Your personal career assistant</p>
          </div>
        </div>
        <div class="flex items-center gap-1">
          <button @click="copilot.startNewSession()" class="p-1.5 text-text-muted hover:text-text-primary hover:bg-slate-800/50 rounded-md transition-colors" title="New Chat">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          </button>
        </div>
      </div>

      <!-- Messages Area -->
      <div class="flex-1 overflow-y-auto p-4 space-y-6 scrollbar-thin" ref="messagesContainer">
        <!-- Empty State -->
        <div v-if="copilot.activeMessages.length === 0" class="h-full flex flex-col items-center justify-center text-center px-2 opacity-70">
          <div class="w-16 h-16 rounded-full bg-blue-500/10 flex items-center justify-center mb-4 text-3xl shadow-inner border border-blue-500/20">🤖</div>
          <h4 class="text-lg font-semibold text-text-primary mb-2">How can I help you today?</h4>
          <p class="text-sm text-text-muted mb-6">I can review your resume, suggest career paths, or prepare you for an interview.</p>
          
          <div class="flex flex-col gap-2 w-full">
            <button @click="messageInput = 'Review my resume and suggest improvements.'" class="text-[11px] text-left px-3 py-2 bg-slate-800/40 hover:bg-slate-700/50 rounded-md border border-slate-700/50 transition-colors">
              "Review my resume and suggest improvements"
            </button>
            <button @click="messageInput = 'What skills should I learn next?'" class="text-[11px] text-left px-3 py-2 bg-slate-800/40 hover:bg-slate-700/50 rounded-md border border-slate-700/50 transition-colors">
              "What skills should I learn next?"
            </button>
            <button @click="messageInput = 'Help me prepare for a behavioral interview.'" class="text-[11px] text-left px-3 py-2 bg-slate-800/40 hover:bg-slate-700/50 rounded-md border border-slate-700/50 transition-colors">
              "Help me prepare for a behavioral interview"
            </button>
          </div>
        </div>

        <!-- Chat History -->
        <div v-for="(msg, i) in copilot.activeMessages" :key="msg.id || i" class="flex gap-3 animate-fade-in" :class="{'flex-row-reverse': msg.role === 'user'}">
          <div class="flex-shrink-0 mt-1">
            <div v-if="msg.role === 'assistant'" class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
          <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm shadow-sm"
               :class="msg.role === 'user' ? 'bg-blue-600 text-white rounded-tr-sm' : 'bg-slate-800 text-slate-200 border border-slate-700 rounded-tl-sm'">
            <div class="prose prose-invert prose-sm max-w-none text-[13px]" v-html="formatMessage(msg.content)"></div>
          </div>
        </div>

        <!-- Typing Indicator -->
        <div v-if="copilot.isTyping" class="flex gap-3 animate-fade-in">
          <div class="flex-shrink-0 mt-1">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
          <div class="bg-slate-800 text-slate-200 border border-slate-700 rounded-2xl rounded-tl-sm px-4 py-3 flex items-center gap-1 w-16 shadow-sm">
            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-4 border-t border-border bg-bg-card/50 backdrop-blur-sm">
        <form @submit.prevent="submitMessage" class="relative flex items-end gap-2">
          <textarea
            v-model="messageInput"
            @keydown.enter.prevent="handleEnter"
            rows="1"
            placeholder="Ask Copilot anything..."
            class="input w-full pr-12 py-3 bg-slate-900/50 resize-none max-h-32 overflow-y-auto text-[13px] rounded-xl"
            style="min-height: 44px;"
          ></textarea>
          <button 
            type="submit" 
            class="absolute right-2 bottom-1.5 w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md"
            :disabled="!messageInput.trim() || copilot.isTyping"
          >
            <svg class="w-4 h-4 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
          </button>
        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { useCopilotStore } from '../../stores/copilot'

const copilot = useCopilotStore()
const messageInput = ref('')
const messagesContainer = ref<HTMLElement | null>(null)

// Auto-scroll to bottom on new messages
watch(() => copilot.activeMessages.length, async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
})

function handleEnter(e: KeyboardEvent) {
  if (e.shiftKey) return // Allow multiline
  submitMessage()
}

async function submitMessage() {
  if (!messageInput.value.trim() || copilot.isTyping) return
  
  const content = messageInput.value.trim()
  messageInput.value = ''
  
  // Reset textarea height if it was auto-expanding
  
  await copilot.sendMessage(content)
}

function formatMessage(content: string) {
  if (!content) return ''
  // Basic markdown formatting: bold, bullet points, line breaks
  let html = content
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\n/g, '<br>')
  
  // Format bullet points (simple)
  html = html.replace(/- (.*?)<br>/g, '<li>$1</li>')
  if (html.includes('<li>')) {
    html = html.replace(/(<li>.*<\/li>)/g, '<ul class="list-disc pl-4 my-2 space-y-1">$1</ul>')
  }
  
  return html
}
</script>

<style scoped>
.chat-window-enter-active,
.chat-window-leave-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.chat-window-enter-from,
.chat-window-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
  transform-origin: bottom right;
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}

.glass-panel {
  background: color-mix(in srgb, var(--bg-surface) 95%, transparent);
}
</style>
