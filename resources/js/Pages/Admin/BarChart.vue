<script setup>
import { onMounted, ref, watch } from 'vue'
import { 
  Chart, 
  BarController, 
  BarElement, 
  CategoryScale, 
  LinearScale, 
  Tooltip, 
  Legend,
  Filler // ADD THIS IMPORT
} from 'chart.js'

// Register all required plugins including Filler
Chart.register(
  BarController, 
  BarElement, 
  CategoryScale, 
  LinearScale, 
  Tooltip, 
  Legend,
  Filler // REGISTER THE FILLER PLUGIN
)

const props = defineProps({
  chartData: Object,
})

const chartRef = ref(null)
let chartInstance = null

onMounted(() => {
  if (chartRef.value && props.chartData) {
    chartInstance = new Chart(chartRef.value, {
      type: 'bar',
      data: props.chartData,
      options: {
        responsive: true,
        plugins: { 
          legend: { 
            position: 'top' 
          } 
        },
        // Remove fill from options since it's in dataset
      },
    })
  }
})

watch(
  () => props.chartData,
  (newData) => {
    if (chartInstance && newData) {
      chartInstance.data = newData
      chartInstance.update()
    }
  }
)
</script>

<template>
  <div class="relative w-full h-80">
    <canvas ref="chartRef"></canvas>
  </div>
</template>