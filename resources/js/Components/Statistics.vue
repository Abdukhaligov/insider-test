<template>
  <table class="table table-striped">
    <thead>
    <tr>
      <th>Team</th>
      <th>PTS</th>
      <th>P</th>
      <th>W</th>
      <th>D</th>
      <th>L</th>
      <th>GD</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="stat in stats" :key="stat.id">
      <td>{{ stat.team.name }}</td>
      <td>{{ stat.points }}</td>
      <td>{{ stat.won + stat.lost + stat.drawn }}</td>
      <td>{{ stat.won }}</td>
      <td>{{ stat.drawn }}</td>
      <td>{{ stat.lost }}</td>
      <td>{{ stat.goal_difference }}</td>
    </tr>
    </tbody>
  </table>
</template>

<script>
export default {
  props: {
    stats: Array,
  }
}
</script>