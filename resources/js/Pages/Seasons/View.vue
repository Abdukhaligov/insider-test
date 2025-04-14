<template>
  <div class="row">
    <div class="overflow-auto mb-4 col-12">
      <div class="float-left">
        <router-link to="/" class="btn btn-primary">
          Go Home
        </router-link>
      </div>
      <div class="float-right">
        <button :disabled="currentWeek > weeks.length" @click="playCurrentWeek" type="button" class="btn btn-primary">
          Play Current Week
        </button>
      </div>
      <div class="float-right mr-4">
        <button :disabled="currentWeek > weeks.length" @click="playAllWeeks" type="button" class="btn btn-primary">
          Play All Weeks
        </button>
      </div>
      <div class="float-right mr-4">
        <button @click="resetData" type="button" class="btn btn-primary">
          Reset Data
        </button>
      </div>
    </div>

    <div class="col-12 p-0 m-0 row row-cols-1 row-cols-md-1 row-cols-lg-2">
      <div class="col-lg-6 mb-4">
        <Statistics :stats="stats"/>
      </div>

      <div class="col-lg-6 mb-4">
        <Predictions :predictions="predictions" />
      </div>
    </div>
    <div class="container col-12">
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <div class="col mb-3" v-for="(week, index) in weeks" :key="index">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white  d-flex justify-content-between align-items-center">
              <div class="float-left">
                <h5 class="card-title mb-0 float-left">Week {{ index + 1 }}</h5>
                <span v-if="currentWeek == (index + 1)" class="card-text ml-3">(current)</span>
              </div>
              <button v-if="currentWeek > index + 1 && !week.edit" type="button" @click="editWeek(index, week)" class="btn btn-secondary float-right btn-sm py-0 px-1">
                Edit
              </button>
              <button v-if="week.edit" type="button" @click="saveWeek(index)" class="btn btn-success float-right btn-sm py-0 px-1">
                Save
              </button>
            </div>

            <div class="mb-3 mt-3">
              <div v-for="(match, matchIndex) in week.matches" :key="matchIndex"
                   class="d-flex mb-1">
                <span class="col-5 p-0 m-0 pl-2 pr-2">{{ match.home_team }}</span>
                
                <input v-if="week.edit" style="width: 25px" :value="match.home_team_score" v-model="weekMatchesForm[index][matchIndex].home_team_score">
                <span v-if="week.edit">-</span>
                <input v-if="week.edit" style="width: 25px" :value="match.away_team_score" v-model="weekMatchesForm[index][matchIndex].away_team_score">

                <span v-if="!week.edit" class="col-2 p-0 m-0">
                  {{ match.home_team_score }}-{{ match.away_team_score }}
                </span>
                <span class="col-5 p-0 m-0">{{ match.away_team }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {useRoute} from 'vue-router'
import axios from 'axios';
import Predictions from "../../Components/Predictions.vue";
import Statistics from "../../Components/Statistics.vue";

export default {
  components: {Statistics, Predictions},
  data() {
    return {
      weekMatchesForm: {},
      teams: [],
      stats: [],
      weeks: [],
      currentWeek: 1,
      seasonId: null,
      predictions: [],
    };
  },
  async created() {
    const route = useRoute();
    this.seasonId = route.params.id;
    await this.getSeasons(route.params.id);
  },
  methods: {
    editWeek(index, week) {
      this.weekMatchesForm[index] = week.matches
      
      this.weeks[index].edit = true;
    },
    async saveWeek(index) {
      await axios.post(`/api/seasons/${this.seasonId}/update-matches`, this.weekMatchesForm[index])
          .then((response) => {
            this.getSeasons(this.seasonId);
          })
          .catch((error) => {
            console.error(error);
          });
      
      this.weeks[index].edit = false;
    },
    playCurrentWeek() {
      axios.get(`/api/seasons/${this.seasonId}/simulate/current-week`)
          .then((response) => {
            this.getSeasons(this.seasonId);
          })
          .catch((error) => {
            console.error(error);
          });
    },
    playAllWeeks() {
      axios.get(`/api/seasons/${this.seasonId}/simulate/all-weeks`)
          .then((response) => {
            this.getSeasons(this.seasonId);
          })
          .catch((error) => {
            console.error(error);
          });
    },
    resetData() {
      axios.get(`/api/seasons/${this.seasonId}/simulate/reset`)
          .then((response) => {
            this.getSeasons(this.seasonId);
          })
          .catch((error) => {
            console.error(error);
          });
    },
    async getSeasons(id) {
      try {
        const {data} = await axios.get(`/api/seasons/${id}`);
        this.currentWeek = data.week;
        this.teams = data.teams;
        this.stats = data.stats;
        this.weeks = data.weeks;
        this.predictions = (await axios.get(`/api/seasons/${id}/predictions`)).data;
      } catch (error) {
        console.log(error);
      }
    }
  },
}
</script>
