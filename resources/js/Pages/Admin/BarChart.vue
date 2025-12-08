<template>
  <div class="chart-container">
    <div v-if="hasValidData" class="relative w-full h-80">
      <canvas ref="chartRef"></canvas>
    </div>
    <div v-else class="h-80 flex items-center justify-center text-slate-500 bg-slate-50 rounded-lg border border-slate-200">
      <div class="text-center">
        <div class="text-4xl mb-2">📊</div>
        <p class="font-medium">No opportunities data for the selected period</p>
        <p class="text-sm text-slate-400 mt-1">
          {{ totalOpportunities > 0 ? 
             `You have ${totalOpportunities} opportunities, but none in the last 7 days.` : 
             'Create your first opportunity to see data here.' 
          }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch, onUnmounted, computed } from 'vue'
import { 
  Chart, 
  BarController, 
  BarElement, 
  CategoryScale, 
  LinearScale, 
  Tooltip, 
  Legend,
  Filler
} from 'chart.js'

// Register all required plugins
Chart.register(
  BarController, 
  BarElement, 
  CategoryScale, 
  LinearScale, 
  Tooltip, 
  Legend,
  Filler
)

const props = defineProps({
  chartData: {
    type: Object,
    required: true,
    default: () => ({
      labels: [],
      datasets: []
    })
  }
})

const chartRef = ref(null)
let chartInstance = null

// Check if we have valid non-zero data
const hasValidData = computed(() => {
  if (!props.chartData.datasets || props.chartData.datasets.length === 0) return false
  const data = props.chartData.datasets[0].data || []
  return data.some(value => value > 0)
})

const totalOpportunities = computed(() => {
  if (!props.chartData.datasets || props.chartData.datasets.length === 0) return 0
  const data = props.chartData.datasets[0].data || []
  return data.reduce((sum, value) => sum + value, 0)
})

// Enhanced chart options with better styling
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        color: '#374151',
        font: {
          size: 12,
          weight: '500'
        }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(255, 255, 255, 0.95)',
      titleColor: '#1f2937',
      bodyColor: '#374151',
      borderColor: '#e5e7eb',
      borderWidth: 1,
      cornerRadius: 8,
      displayColors: true,
      callbacks: {
        label: function(context) {
          return `${context.dataset.label}: ${context.parsed.y}`
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: false,
        drawBorder: false
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
        }
      }
    },
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
        drawBorder: false
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
        },
        stepSize: 1
      }
    }
  },
  animation: {
    duration: 1000,
    easing: 'easeOutQuart'
  }
}

// Initialize chart with enhanced styling
const initializeChart = () => {
  if (!chartRef.value) return
  
  // Destroy existing chart if it exists
  if (chartInstance) {
    chartInstance.destroy()
  }

  // Check if we have valid data
  if (props.chartData && props.chartData.labels && props.chartData.labels.length > 0) {
    
    // Enhance the dataset with better styling
    const enhancedDatasets = props.chartData.datasets.map(dataset => ({
      ...dataset,
      backgroundColor: createGradient(chartRef.value), // Use gradient
      borderColor: '#0d9488', // Teal border
      borderWidth: 2,
      borderRadius: 8,
      borderSkipped: false,
      hoverBackgroundColor: '#0f766e', // Darker teal on hover
      hoverBorderColor: '#0d9488',
      barPercentage: 0.6,
      categoryPercentage: 0.8
    }))

    const enhancedChartData = {
      ...props.chartData,
      datasets: enhancedDatasets
    }

    chartInstance = new Chart(chartRef.value, {
      type: 'bar',
      data: enhancedChartData,
      options: chartOptions
    })
  }
}

// Create gradient for bars
const createGradient = (ctx) => {
  if (!ctx) return '#14b8a6'
  
  const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400)
  gradient.addColorStop(0, '#5eead4') // Light teal
  gradient.addColorStop(0.7, '#14b8a6') // Medium teal
  gradient.addColorStop(1, '#0d9488') // Dark teal
  return gradient
}

// Watch for chart data changes
watch(
  () => props.chartData,
  (newData) => {
    if (newData && newData.labels && newData.labels.length > 0) {
      // Small delay to ensure DOM is ready
      setTimeout(() => {
        initializeChart()
      }, 100)
    }
  },
  { deep: true, immediate: false }
)

// Initialize when component mounts
onMounted(() => {
  // Small delay to ensure DOM is fully rendered
  setTimeout(() => {
    initializeChart()
  }, 300)
})

// Clean up on unmount
onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy()
  }
})
</script>

<style scoped>
.chart-container {
  width: 100%;
  min-height: 320px;
}
</style>