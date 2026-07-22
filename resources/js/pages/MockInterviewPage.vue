<template>
  <PageLayout title="AI Mock Interview" subtitle="Practice your interview skills with our AI recruiter">
    <template #actions>
      <button class="btn btn-primary" @click="showSetupModal = true">Start New Interview</button>
    </template>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
    
    <div v-else-if="!interviews.length" class="text-center py-16 card mt-6">
      <div class="text-6xl mb-4">🎤</div>
      <h3 class="text-xl font-bold text-white mb-2">No Interviews Yet</h3>
      <p class="text-slate-400 mb-6 max-w-md mx-auto">Practice behavioral, technical, or HR interviews with an AI that mimics a real recruiter, complete with detailed feedback.</p>
      <button class="btn btn-primary" @click="showSetupModal = true">Start Mock Interview</button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
      <div v-for="interview in interviews" :key="interview.id" class="card hover:border-blue-500/30 transition-colors flex flex-col h-full cursor-pointer" @click="openInterview(interview.id)">
        <div class="flex items-center justify-between mb-4">
          <div class="badge" :class="interview.status === 'completed' ? 'badge-green' : 'badge-blue'">
            {{ interview.status === 'completed' ? 'Completed' : 'In Progress' }}
          </div>
          <span class="text-xs text-slate-500">{{ new Date(interview.created_at).toLocaleDateString() }}</span>
        </div>
        
        <h3 class="text-lg font-bold text-white">{{ interview.job_title }}</h3>
        <p class="text-sm text-blue-400 capitalize mb-4">{{ interview.interview_type }} Interview</p>
        
        <div v-if="interview.status === 'completed' && interview.final_score" class="mt-auto pt-4 border-t border-white/05 grid grid-cols-3 gap-2 text-center">
          <div>
            <div class="text-lg font-bold text-white">{{ interview.final_score.technical_accuracy }}/100</div>
            <div class="text-[10px] text-slate-400 uppercase">Technical</div>
          </div>
          <div>
            <div class="text-lg font-bold text-white">{{ interview.final_score.communication_skills }}/100</div>
            <div class="text-[10px] text-slate-400 uppercase">Comm.</div>
          </div>
          <div>
            <div class="text-lg font-bold text-white">{{ interview.final_score.confidence }}/100</div>
            <div class="text-[10px] text-slate-400 uppercase">Confidence</div>
          </div>
        </div>
        <div v-else class="mt-auto pt-4 border-t border-white/05">
          <button class="btn btn-secondary w-full">Continue Interview →</button>
        </div>
      </div>
    </div>

    <!-- Setup Modal -->
    <Modal v-if="showSetupModal" :show="showSetupModal" @close="showSetupModal = false" title="Start Mock Interview">
      <form @submit.prevent="startInterview" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Target Job Title</label>
          <input v-model="form.job_title" type="text" class="input" placeholder="e.g. Fullstack Developer" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1">Interview Type</label>
          <select v-model="form.interview_type" class="input" required>
            <option value="behavioral">Behavioral / Culture Fit</option>
            <option value="technical">Technical / System Design</option>
            <option value="hr">HR Screening</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" class="btn btn-secondary" @click="showSetupModal = false">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="starting">
            <span v-if="starting" class="animate-spin mr-2">⏳</span>
            {{ starting ? 'Preparing AI...' : 'Start Interview' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Active Interview Modal / Overlay -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="activeInterview" class="fixed inset-0 z-[100] flex flex-col" style="background: var(--bg-base)">
          <div class="flex items-center justify-between p-4 border-b" style="background: var(--bg-surface); border-color: var(--border)">
            <div>
              <h2 class="text-lg font-bold text-white">{{ activeInterview.job_title }}</h2>
              <p class="text-xs text-blue-400 capitalize">{{ activeInterview.interview_type }} Interview Simulator</p>
            </div>
            <div class="flex items-center gap-3">
              <button @click="voiceEnabled = !voiceEnabled" class="btn btn-secondary btn-sm flex items-center gap-1.5" :title="voiceEnabled ? 'Mute AI Voice' : 'Enable AI Voice'">
                <span>{{ voiceEnabled ? '🔊 AI Voice: ON' : '🔇 AI Voice: OFF' }}</span>
              </button>
              <button v-if="activeInterview.status !== 'completed'" @click="finishInterview" class="btn btn-primary btn-sm" :disabled="finishing">
                {{ finishing ? 'Analyzing...' : 'End & Get Feedback' }}
              </button>
              <button @click="activeInterview = null" class="p-2 text-slate-400 hover:text-white rounded-md transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </div>

          <div v-if="activeInterview.status === 'completed' && activeInterview.final_score" class="flex-1 overflow-y-auto p-6 flex justify-center bg-slate-900/50">
            <div class="max-w-2xl w-full space-y-6">
              <div class="card bg-blue-500/10 border-blue-500/30 text-center py-8">
                <h1 class="text-3xl font-black text-white mb-2">Interview Complete</h1>
                <p class="text-blue-200">Here is your AI evaluation</p>
              </div>
              
              <div class="grid grid-cols-3 gap-4">
                <div class="glass p-4 rounded-xl border border-white/05 text-center">
                  <div class="text-3xl font-bold text-white">{{ activeInterview.final_score.technical_accuracy }}<span class="text-sm text-slate-500">/100</span></div>
                  <div class="text-xs text-slate-400 uppercase tracking-wider mt-1">Technical</div>
                </div>
                <div class="glass p-4 rounded-xl border border-white/05 text-center">
                  <div class="text-3xl font-bold text-white">{{ activeInterview.final_score.communication_skills }}<span class="text-sm text-slate-500">/100</span></div>
                  <div class="text-xs text-slate-400 uppercase tracking-wider mt-1">Communication</div>
                </div>
                <div class="glass p-4 rounded-xl border border-white/05 text-center">
                  <div class="text-3xl font-bold text-white">{{ activeInterview.final_score.confidence }}<span class="text-sm text-slate-500">/100</span></div>
                  <div class="text-xs text-slate-400 uppercase tracking-wider mt-1">Confidence</div>
                </div>
              </div>

              <div class="card space-y-4">
                <h3 class="font-bold text-white">Detailed Feedback</h3>
                <p class="text-slate-300 text-sm leading-relaxed">{{ activeInterview.final_score.feedback }}</p>
                
                <h4 class="font-semibold text-white mt-6 mb-2 flex items-center gap-2"><svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Areas for Improvement</h4>
                <ul class="list-disc list-inside space-y-1">
                  <li v-for="imp in activeInterview.final_score.improvements" :key="imp" class="text-sm text-slate-300">{{ imp }}</li>
                </ul>
              </div>
            </div>
          </div>

          <div v-else class="flex-1 overflow-hidden flex flex-col max-w-4xl mx-auto w-full border-x border-border shadow-2xl glass-panel">
            <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6" ref="chatContainer">
              <div v-for="msg in activeMessages" :key="msg.id" class="flex gap-4" :class="{'flex-row-reverse': msg.role === 'user'}">
                <div class="flex-shrink-0 mt-1">
                  <div v-if="msg.role === 'assistant' || msg.role === 'ai'" class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <span class="text-lg">👔</span>
                  </div>
                  <div v-else class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center font-bold text-slate-300 shadow-lg">
                    You
                  </div>
                </div>
                <div class="max-w-[80%] rounded-2xl px-5 py-4 shadow-sm"
                     :class="msg.role === 'user' ? 'bg-blue-600 text-white rounded-tr-sm' : 'bg-slate-800 text-slate-200 border border-slate-700 rounded-tl-sm'">
                  <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                </div>
              </div>
              
              <div v-if="typing" class="flex gap-4">
                <div class="flex-shrink-0 mt-1">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <span class="text-lg">👔</span>
                  </div>
                </div>
                <div class="bg-slate-800 border border-slate-700 rounded-2xl rounded-tl-sm px-5 py-4 w-20 flex items-center justify-center gap-1.5 shadow-sm">
                  <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                  <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                  <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </div>
              </div>
            </div>
            
            <div class="p-4 border-t border-border bg-bg-card">
              <form @submit.prevent="sendMessage" class="relative flex items-end gap-2">
                <textarea
                  v-model="chatInput"
                  @keydown.enter.prevent="sendMessage"
                  rows="2"
                  :placeholder="isListening ? 'Listening to your voice... Speak now.' : 'Type or click 🎙️ to speak your answer...'"
                  class="input w-full pr-24 py-3 bg-slate-900 resize-none"
                  :class="{'border-red-500 ring-2 ring-red-500/30': isListening}"
                ></textarea>
                <div class="absolute right-2 bottom-2 flex items-center gap-1">
                  <button
                    type="button"
                    @click="toggleMic"
                    class="w-10 h-10 rounded-lg flex items-center justify-center transition-all shadow-md"
                    :class="isListening ? 'bg-red-600 text-white animate-pulse' : 'bg-slate-800 hover:bg-slate-700 text-slate-300'"
                    :title="isListening ? 'Click to Stop Microphone' : 'Click to Speak via Microphone'"
                  >
                    🎙️
                  </button>
                  <button 
                    type="submit" 
                    class="w-10 h-10 rounded-lg bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center transition-colors disabled:opacity-50 shadow-md"
                    :disabled="!chatInput.trim() || typing"
                  >
                    <svg class="w-5 h-5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </PageLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue'
import PageLayout from '../components/layout/PageLayout.vue'
import Modal from '../components/ui/Modal.vue'
import api from '../services/api'

const interviews = ref<any[]>([])
const loading = ref(true)
const showSetupModal = ref(false)
const starting = ref(false)
const form = ref({ job_title: '', interview_type: 'behavioral' })

const activeInterview = ref<any>(null)
const activeMessages = ref<any[]>([])
const chatInput = ref('')
const typing = ref(false)
const finishing = ref(false)
const chatContainer = ref<HTMLElement | null>(null)

onMounted(async () => {
  await fetchInterviews()
})

watch(() => activeMessages.value.length, async () => {
  await nextTick()
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight
  }
})

async function fetchInterviews() {
  loading.value = true
  try {
    const res = await api.get('/api/v1/mock-interviews')
    interviews.value = res.data
  } catch (e) {
    console.error('Failed to fetch interviews', e)
  } finally {
    loading.value = false
  }
}

async function startInterview() {
  starting.value = true
  try {
    const res = await api.post('/api/v1/mock-interviews', form.value)
    interviews.value.unshift(res.data)
    showSetupModal.value = false
    form.value = { job_title: '', interview_type: 'behavioral' }
    openInterview(res.data.id)
  } catch (e) {
    alert('Failed to start interview')
  } finally {
    starting.value = false
  }
}

const isListening = ref(false)
const voiceEnabled = ref(true)
let recognition: any = null

function toggleMic() {
  if (isListening.value) {
    if (recognition) recognition.stop()
    isListening.value = false
    return
  }
  
  const SpeechRecognition = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition
  if (!SpeechRecognition) {
    alert('Microphone speech recognition is supported in Chrome, Edge, and Safari.')
    return
  }
  
  recognition = new SpeechRecognition()
  recognition.continuous = false
  recognition.interimResults = true
  recognition.lang = 'en-US'
  
  recognition.onstart = () => {
    isListening.value = true
  }
  
  recognition.onresult = (event: any) => {
    let transcript = ''
    for (let i = event.resultIndex; i < event.results.length; i++) {
      transcript += event.results[i][0].transcript
    }
    chatInput.value = transcript
  }
  
  recognition.onerror = () => {
    isListening.value = false
  }
  
  recognition.onend = () => {
    isListening.value = false
  }
  
  recognition.start()
}

function speakText(text: string) {
  if (!voiceEnabled.value || !('speechSynthesis' in window)) return
  window.speechSynthesis.cancel()
  const utterance = new SpeechSynthesisUtterance(text)
  utterance.rate = 1.0
  utterance.pitch = 1.0
  window.speechSynthesis.speak(utterance)
}

async function openInterview(id: number) {
  try {
    const res = await api.get(`/api/v1/mock-interviews/${id}`)
    activeInterview.value = res.data
    activeMessages.value = res.data.messages || []
    
    // Speak latest AI prompt if active
    if (activeMessages.value.length) {
      const lastMsg = activeMessages.value[activeMessages.value.length - 1]
      if (lastMsg.role === 'assistant' || lastMsg.role === 'ai') {
        speakText(lastMsg.content)
      }
    }
  } catch (e) {
    alert('Failed to load interview')
  }
}

async function sendMessage() {
  if (!chatInput.value.trim() || typing.value) return
  
  const content = chatInput.value
  chatInput.value = ''
  if (isListening.value && recognition) {
    recognition.stop()
    isListening.value = false
  }
  
  const tempId = Date.now()
  activeMessages.value.push({ id: tempId, role: 'user', content })
  typing.value = true
  
  try {
    const res = await api.post(`/api/v1/mock-interviews/${activeInterview.value.id}/chat`, { message: content })
    activeMessages.value = res.data.messages
    
    // Speak AI response
    if (activeMessages.value.length) {
      const lastMsg = activeMessages.value[activeMessages.value.length - 1]
      if (lastMsg.role === 'assistant' || lastMsg.role === 'ai') {
        speakText(lastMsg.content)
      }
    }
  } catch (e) {
    activeMessages.value = activeMessages.value.filter(m => m.id !== tempId)
    alert('Failed to send message')
  } finally {
    typing.value = false
  }
}

async function finishInterview() {
  if (!confirm('Are you ready to end the interview and receive feedback?')) return
  finishing.value = true
  try {
    const res = await api.post(`/api/v1/mock-interviews/${activeInterview.value.id}/finish`)
    activeInterview.value = res.data
    // Update list
    const idx = interviews.value.findIndex(i => i.id === activeInterview.value.id)
    if (idx !== -1) interviews.value[idx] = res.data
  } catch (e) {
    alert('Failed to generate feedback')
  } finally {
    finishing.value = false
  }
}
</script>
