<template>
  <div class="container-fluid" v-if="!loading">
    <div v-if="seasons?.length > 0">
      <div class="overflow-auto mb-4">
        <div class="d-flex flex-column align-items-center justify-content-center float-right">
          <router-link to="/seasons/create" class="btn btn-primary">
            Add Season
          </router-link>
        </div>
      </div>
      
      <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-4">
        <div v-for="season in seasons" :key="season.id" class="col mb-3">
          <div class="card shadow-lg border-0 transition-all hover-shadow-lg h-100">
            <div class="card-header bg-primary text-white py-3">
              <div class="d-flex justify-content-between align-items-center">
                <h3 class="h5 mb-0">Season #{{ season.id }}</h3>
                <span class="badge bg-white text-primary">{{ season.date }}</span>
              </div>
            </div>
            <div class="card-body py-4">
              <div class="d-grid row">
                <div class="col-8">
                  <router-link :to="'/seasons/' + season.id" class="btn btn-outline-primary btn-lg py-2">
                    {{ season.matches }} Matches
                  </router-link>
                </div>
                
                <div class="col-4 d-flex justify-content-between align-items-center">
                  <div>{{ season.weeks }} weeks</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="d-flex flex-column align-items-center justify-content-center min-vh-50">
      <div class="text-center mb-4">
        <i class="fas fa-calendar-plus fa-4x text-muted mb-3"></i>
        <h2 class="h4 text-muted mb-3">No Seasons Found</h2>
      </div>
      <router-link
          to="/seasons/create"
          class="btn btn-primary btn-lg px-5"
      >
        Create First Season
      </router-link>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Dashboard',
  data() {
    return {
      message: "",
      seasons: [],
      loading: true,
    };
  },
  async created() {
    await this.getSeasons();
  },
  methods: {
    async getSeasons() {
      try {
        const {data} = await axios.get(`/api/seasons`);
        this.seasons = data;
        this.loading = false;
      } catch (error) {
        console.error(error);
      }
    }
  }
}
</script>

<style scoped>
.hover-shadow-lg {
  transition: transform 0.2s, box-shadow 0.2s;
}

.hover-shadow-lg:hover {
  transform: translateY(-5px);
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
}

.min-vh-50 {
  min-height: 50vh;
}
</style>