<template>
  <div>
    <h5>Predictions</h5>
    <div v-for="(pred, index) in predictions" :key="index" class="mb-2">
      <div class="d-flex justify-content-between">
        <span>{{ pred.name }}</span>
        <span>{{ pred.percentage }}%</span>
      </div>
      <div class="progress" style="height: 20px;">
        <div class="progress-bar"
             :style="{ width: pred.percentage + '%' }">
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    predictions: Array,
  }
}
</script>