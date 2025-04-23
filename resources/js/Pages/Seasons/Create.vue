<template>
  <div class="container">
    <div class="row mb-4">
      <div class="col-12">
        <button :disabled="!teams.find(t => !selectedTeams.map(t => t.id).includes(t.id))" @click="addTeam" type="button" class="btn btn-primary">
          + Add Team
        </button>

        <button :disabled="selectedTeams.length <= 1" @click="startSeason" type="button" class="btn btn-primary float-right ml-4">
          Start Season
        </button>

        <button @click="goHome" type="button" class="btn btn-primary float-right">
          Go Home
        </button>
      </div>
    </div>

    <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-4">
      <div v-for="(selectedTeam, index) in selectedTeams" :key="index" class="col mb-4">
        <div class="card shadow-lg border-0 transition-all hover-shadow-lg h-100">
          
          <div class="remove-button-container">
            <button type="button" class="btn btn-danger btn-sm rounded-circle px-2" @click="removeTeam(selectedTeam)">X</button>
          </div>
          
          <div class="card-body py-4">
            <div class="d-grid row">
              <div class="col-7">
                <label class="form-label">Team</label>
                <select
                    class="form-control"
                    v-model="selectedTeam.id"
                    @change="handleChange(selectedTeam.id, index)"
                >
                  <option :value="null" disabled>Select a team</option>
                  <option :disabled="selectedTeams.some((t, i) => t.id === team.id && i !== index)" v-for="team in teams" :key="team.id" :value="team.id">
                    {{ team.name }}
                  </option>
                </select>
              </div>

              <div class="col-5">
                <label class="form-label">Power</label>
                <input
                    v-model="selectedTeam.strength"
                    type="number"
                    class="form-control"
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      message: "",
      teams: [],
      selectedTeams: [],
    };
  },
  methods: {
    handleChange(teamId, index) {
      const isDuplicate = this.selectedTeams.some((t, i) => t.id === teamId && i !== index);
      
      if (isDuplicate) {
        alert("This team is already selected.");
        this.selectedTeams[index].id = null;
        this.selectedTeams[index].strength = null;
        return;
      }

      const team = this.teams.find(t => t.id == teamId);
      if (team) {
        this.selectedTeams[index].id = team.id;
        this.selectedTeams[index].strength = team.strength ?? 0;
      }
    },
    addTeam() {
      const selectedIds = this.selectedTeams.map(t => t.id);
      const availableTeam = this.teams.find(t => !selectedIds.includes(t.id));

      if (availableTeam) {
        this.selectedTeams.push({
          id: availableTeam.id,
          strength: availableTeam.strength ?? 0,
        });
      } else {
        alert('All teams are already selected.');
      }
    },
    async loadTeams() {
      try {
        const {data} = await axios.get(`/api/teams`);
        this.teams = data;

        // Automatically add teams to selectedTeams by default
        this.selectedTeams = this.teams.slice(0, 4).map(team => ({
          id: team.id,
          strength: team.strength ?? 0,
        }));
      } catch (error) {
        console.error(error);
      }
    },
    removeTeam(team) {
      this.selectedTeams.splice(this.selectedTeams.indexOf(team), 1);
    },
    async startSeason() {
      if (this.selectedTeams.length <= 1) {
        alert(`Might be selected at least 2 teams.`);
      }

      try {
        const { data } = await axios.post('/api/seasons', this.selectedTeams);
        this.$router.push('/seasons/' + data.id);
      } catch (error) {
        console.error(error);
      }
    },
    goHome() {
      this.$router.push('/');
    }
  },
  computed: {
    findTeam(id) {
      return this.teams.find(team => team.id === id);
    }
  },
  async created() {
    await this.loadTeams();
  }
}
</script>

<style scoped>
.remove-button-container {
  position: absolute;
  right: -10px;
  top: -15px;
}
.remove-button-container button {
 font-size: 10px;
}
</style>
