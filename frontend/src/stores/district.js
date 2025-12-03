import { defineStore } from 'pinia'

export const useDistrictStore = defineStore('district', {
  state: () => ({
    currentDistrict: localStorage.getItem('currentDistrict') || null,
    districts: [
      {
        name: 'Besut',
        slug: 'besut',
        pbt: 'Majlis Daerah Besut',
        abbreviation: 'MDB',
        color: '#DC143C',
        phone: '609 6956 388',
        email: 'info@mdb.terengganu.gov.my',
        address: 'Majlis Daerah Besut, 22000 Jertih, Terengganu',
        website: 'https://mdb.terengganu.gov.my',
        facebook: 'https://www.facebook.com/MDBesut',
        instagram: 'https://www.instagram.com/mdbesut'
      },
      {
        name: 'Marang',
        slug: 'marang',
        pbt: 'Majlis Daerah Marang',
        abbreviation: 'MDM',
        color: '#8B008B',
        phone: '+609 618 2366',
        email: 'info@mdm.terengganu.gov.my',
        address: 'Majlis Daerah Marang, 21600 Marang, Terengganu',
        website: 'https://mdm.terengganu.gov.my',
        facebook: 'https://www.facebook.com/profile.php?id=100081313411458',
        instagram: ''
      },
      {
        name: 'Setiu',
        slug: 'setiu',
        pbt: 'Majlis Daerah Setiu',
        abbreviation: 'MDS',
        color: '#8B7355',
        phone: '+609-609 9377',
        email: 'info@mds.terengganu.gov.my',
        address: 'Majlis Daerah Setiu, 22100 Permaisuri, Terengganu',
        website: 'https://mds.terengganu.gov.my',
        facebook: 'https://www.facebook.com/MDSetiu',
        instagram: ''
      },
      {
        name: 'Hulu Terengganu',
        slug: 'hulu-terengganu',
        pbt: 'Majlis Daerah Hulu Terengganu',
        abbreviation: 'MDHT',
        color: '#FF8C00',
        phone: '+609-6811 149',
        email: 'info@mdht.terengganu.gov.my',
        address: 'Majlis Daerah Hulu Terengganu, 21700 Kuala Berang, Terengganu',
        website: 'https://mdht.terengganu.gov.my',
        facebook: 'https://www.facebook.com/MDHuluTerengganu',
        instagram: ''
      }
      ,
      // New PBTs
      {
        name: 'Kuala Terengganu',
        slug: 'kuala-terengganu',
        pbt: 'Majlis Bandaraya Kuala Terengganu',
        abbreviation: 'MBKT',
        color: '#EEBF04',
        phone: '+609-612 1111',
        email: 'info@mbkt.terengganu.gov.my',
        address: 'Majlis Bandaraya Kuala Terengganu, 20000 Kuala Terengganu, Terengganu',
        website: 'https://mbkt.terengganu.gov.my',
        facebook: 'https://www.facebook.com/mbktrg',
        instagram: ''
      },
      {
        name: 'Kemaman',
        slug: 'kemaman',
        pbt: 'Majlis Perbandaran Kemaman',
        abbreviation: 'MPK',
        color: '#1E3A8A',
        phone: '+609-8597 777',
        email: 'info@mpk.terengganu.gov.my',
        address: 'Majlis Perbandaran Kemaman, 24000 Kemaman, Terengganu',
        website: 'https://mpk.terengganu.gov.my',
        facebook: 'https://www.facebook.com/MPKemaman.02',
        instagram: 'https://www.instagram.com/mpkemaman'
      },
      {
        name: 'Dungun',
        slug: 'dungun',
        pbt: 'Majlis Perbandaran Dungun',
        abbreviation: 'MPD',
        color: '#06B6D4',
        phone: '+609-8481931',
        email: 'info@mpd.terengganu.gov.my',
        address: 'Majlis Perbandaran Dungun, 23000 Dungun, Terengganu',
        website: 'https://mpd.terengganu.gov.my',
        facebook: 'https://www.facebook.com/MPDungun',
        instagram: 'https://www.instagram.com/mpdungun'
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
    },
    districtPhone: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.phone : '+60 9-626 1333'
    },
    districtEmail: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.email : 'info@mdht.terengganu.gov.my'
    },
    districtAddress: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.address : 'Majlis Daerah Hulu Terengganu, 21700 Kuala Berang, Terengganu'
    },
    districtWebsite: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.website : 'https://mdht.terengganu.gov.my'
    },
    districtFacebook: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.facebook : 'https://www.facebook.com/MDHuluTerengganu'
    },
    districtInstagram: (state) => {
      const district = state.districts.find(d => d.name === state.currentDistrict)
      return district ? district.instagram : 'https://www.instagram.com/mdhuluterengganu'
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
      this.currentDistrict = null
      localStorage.removeItem('currentDistrict')
    }
  }
})
