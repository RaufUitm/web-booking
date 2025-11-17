import { defineStore } from 'pinia'

export const useDistrictStore = defineStore('district', {
  state: () => ({
    currentDistrict: localStorage.getItem('currentDistrict') || 'Hulu Terengganu',
    districts: [
      {
        name: 'Besut',
        slug: 'besut',
        pbt: 'Majlis Daerah Besut',
        abbreviation: 'MDB',
        color: '#DC143C'
      },
      {
        name: 'Marang',
        slug: 'marang',
        pbt: 'Majlis Daerah Marang',
        abbreviation: 'MDM',
        color: '#8B008B'
      },
      {
        name: 'Setiu',
        slug: 'setiu',
        pbt: 'Majlis Daerah Setiu',
        abbreviation: 'MDS',
        color: '#8B7355'
      },
      {
        name: 'Hulu Terengganu',
        slug: 'hulu-terengganu',
        pbt: 'Majlis Daerah Hulu Terengganu',
        abbreviation: 'MDHT',
        color: '#FF8C00'
      }
    ]
  }),

  getters: {
    districtInfo: (state) => {
      return state.districts.find(d => d.name === state.currentDistrict) || state.districts[3]
    },
    districtName: (state) => state.currentDistrict,
    districtAbbreviation: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.abbreviation : 'MDHT'
    },
    districtColor: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.color : '#FF8C00'
    },
    pbtName: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.pbt : 'Majlis Daerah Hulu Terengganu'
    }
  },

  actions: {
    setDistrict(districtName) {
      this.currentDistrict = districtName
      localStorage.setItem('currentDistrict', districtName)
    },

    setDistrictBySlug(slug) {
      const district = this.districts.find(d => d.slug === slug)
      if (district) {
        this.setDistrict(district.name)
      }
    },

    clearDistrict() {
      this.currentDistrict = 'Hulu Terengganu'
      localStorage.removeItem('currentDistrict')
    }
  }
})
