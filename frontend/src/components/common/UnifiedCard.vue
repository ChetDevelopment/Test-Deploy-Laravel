<script setup lang="ts">
import { computed } from 'vue';

export interface CardProps {
  title?: string;
  subtitle?: string;
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  padding?: 'sm' | 'md' | 'lg';
  border?: boolean;
  shadow?: boolean;
  hoverable?: boolean;
  loading?: boolean;
  error?: string;
}

const props = withDefaults(defineProps<CardProps>(), {
  variant: 'default',
  size: 'md',
  padding: 'md',
  border: true,
  shadow: true,
  hoverable: true,
  loading: false,
});

const cardClasses = computed(() => [
  'card',
  {
    'border-slate-200': props.border,
    'shadow-md': props.shadow,
    'hover:shadow-lg': props.hoverable,
    'bg-white': props.variant === 'default',
    'bg-primary/5 border-primary/20': props.variant === 'primary',
    'bg-success/5 border-success/20': props.variant === 'success',
    'bg-warning/5 border-warning/20': props.variant === 'warning',
    'bg-danger/5 border-danger/20': props.variant === 'danger',
  }
]);

const paddingClasses = computed(() => {
  switch (props.padding) {
    case 'sm': return 'p-3';
    case 'lg': return 'p-8';
    default: return 'p-6';
  }
});

const titleClasses = computed(() => [
  'font-bold',
  {
    'text-lg': props.size === 'sm',
    'text-2xl': props.size === 'lg',
    'text-xl': props.size === 'md',
  }
]);

const subtitleClasses = computed(() => [
  'text-slate-500',
  {
    'text-sm': props.size === 'sm',
    'text-base': props.size === 'md' || props.size === 'lg',
  }
]);
</script>

<template>
  <div :class="cardClasses" class="transition-all duration-300">
    <!-- Card Header -->
    <div v-if="$slots.header || title || subtitle" class="card-header" :class="paddingClasses">
      <slot name="header">
        <div v-if="title || subtitle" class="space-y-1">
          <h3 :class="titleClasses" class="text-slate-900 dark:text-white">{{ title }}</h3>
          <p v-if="subtitle" :class="subtitleClasses" class="text-slate-600 dark:text-slate-400">{{ subtitle }}</p>
        </div>
      </slot>
    </div>

    <!-- Divider -->
    <div v-if="($slots.header || title || subtitle) && ($slots.default || loading || error)" class="border-t border-slate-200 dark:border-slate-700"></div>

    <!-- Card Body -->
    <div v-if="$slots.default || loading || error" class="card-body" :class="paddingClasses">
      <slot name="default">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          <span class="ml-3 text-slate-600 dark:text-slate-400">Loading...</span>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <div class="size-8 bg-red-100 rounded-full flex items-center justify-center">
              <svg class="size-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div>
              <p class="font-medium text-red-900">Error</p>
              <p class="text-sm text-red-600">{{ error }}</p>
            </div>
          </div>
        </div>

        <!-- Default Content -->
        <div v-else>
          <slot></slot>
        </div>
      </slot>
    </div>

    <!-- Divider -->
    <div v-if="($slots.header || title || subtitle || $slots.default || loading || error) && $slots.footer" class="border-t border-slate-200 dark:border-slate-700"></div>

    <!-- Card Footer -->
    <div v-if="$slots.footer" class="card-footer" :class="paddingClasses">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<style scoped>
/* Additional styles for the unified card */
.card {
  transition: transform var(--transition-fast), box-shadow var(--transition-normal);
}

.card:hover {
  transform: translateY(-2px);
}

/* Dark theme overrides */
.dark .card {
  background-color: var(--slate-800);
  border-color: var(--slate-700);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .card-header,
  .card-body,
  .card-footer {
    padding: var(--space-4);
  }
}
</style>