<template>
  <div>
    <div class="container">
      <div class="overflow-auto mb-4">
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
          <button @click="resetData" type="button" class="btn btn-primary">
            Reset Data
          </button>
        </div>
      </div>

      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <div class="col mb-3" v-for="(week, index) in weeks" :key="index">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white  d-flex justify-content-between align-items-center">
              <div class="float-left">
                <h5 class="card-title mb-0 float-left">Week {{ index + 1 }}</h5>
                <span v-if="currentWeek == (index + 1)" class="card-text ml-3">(current)</span>
              </div>
              <button v-if="currentWeek > index + 1" type="button" class="btn btn-secondary float-right btn-sm py-0 px-1">
                Edit
              </button>
            </div>

            <div class="mb-3 mt-3">
              <div v-for="(match, matchIndex) in week.matches" :key="matchIndex"
                   class="d-flex mb-1">
                <span class="col-5 p-0 m-0 pl-2 pr-2">{{ match.home_team.name }}</span>
                <span class="col-2 p-0 m-0">{{ match.home_team_score }}-{{ match.away_team_score }}</span>
                <span class="col-5 p-0 m-0">{{ match.away_team.name }}</span>
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

export default {
  data() {
    return {
      teams: [],
      matches: [],
      currentWeek: 1,
      seasonId: null,
    };
  },
  async created() {
    const route = useRoute();
    this.seasonId = route.params.id;
    await this.getSeasons(route.params.id);
  },
  methods: {
    groupBy(list, keyGetter) {
      const map = [];
      list.forEach((item) => {
        const key = keyGetter(item);
        const collection = map[key];
        if (!collection) {
          map[key] = [item];
        } else {
          collection.push(item);
        }
      });
      return map;
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
        this.matches = this.groupBy(data.matches, x => x.week - 1);
      } catch (error) {
        console.log(error);
      }
    }
  },
  computed: {
    weeks() {
      return this.matches.map(week => {
        const matches = week.map(match => {
          return {
            ...match,
            home: this.teams.find(team => team.id === match.home_team_id),
            away: this.teams.find(team => team.id === match.away_team_id)
          }
        });
        return {...week, matches}
      })
    }
  }
}
</script>
