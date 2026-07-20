<script setup lang="ts">
const props = defineProps<{ status: string }>()

const MAP: Record<string, { cls: string; label: string }> = {
    pending:       { cls: 'badge-gray',   label: 'Pending' },
    processing:    { cls: 'badge-blue',   label: 'Processing' },
    completed:     { cls: 'badge-green',  label: 'Completed' },
    failed:        { cls: 'badge-red',    label: 'Failed' },
    needs_review:  { cls: 'badge-yellow', label: 'Review' },
    saved:         { cls: 'badge-gray',   label: 'Saved' },
    applied:       { cls: 'badge-blue',   label: 'Applied' },
    assessment:    { cls: 'badge-indigo', label: 'Assessment' },
    interview:     { cls: 'badge-yellow', label: 'Interview' },
    offer:         { cls: 'badge-green',  label: 'Offer' },
    accepted:      { cls: 'badge-green',  label: 'Accepted' },
    rejected:      { cls: 'badge-red',    label: 'Rejected' },
    withdrawn:     { cls: 'badge-gray',   label: 'Withdrawn' },
    active:        { cls: 'badge-green',  label: 'Active' },
    extracted:     { cls: 'badge-blue',   label: 'Extracted' },
    professional:  { cls: 'badge-blue',   label: 'Professional' },
    friendly:      { cls: 'badge-green',  label: 'Friendly' },
    executive:     { cls: 'badge-indigo', label: 'Executive' },
    technical:     { cls: 'badge-purple', label: 'Technical' },
}
const entry = (s: string) => MAP[s] ?? { cls: 'badge-gray', label: s }
</script>

<template>
  <span :class="['badge', entry(status).cls]">{{ entry(status).label }}</span>
</template>
