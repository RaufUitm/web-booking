import { useDistrictStore } from '@/stores/district'

export default function useDistrictRoutes() {
  const districtStore = useDistrictStore()

  const getDistrictSlug = () => {
    const current = districtStore.districtInfo
    // prefer slug if available
    return current?.slug || current?.name?.toLowerCase()?.replace(/\s+/g, '-') || 'hulu-terengganu'
  }

  const prefixPath = (path) => {
    const slug = getDistrictSlug()
    if (!path) return `/${slug}`
    if (path.startsWith('/')) return `/${slug}${path}`
    return `/${slug}/${path}`
  }

  const bookingPath = (facilityId) => prefixPath(`/booking/${facilityId}`)
  const facilityDetailPath = (id) => prefixPath(`/facilities/${id}`)
  const loginPath = (redirect = '/') => {
    const slug = getDistrictSlug()
    const redirectUrl = encodeURIComponent(redirect)
    return `/${slug}/login?redirect=${redirectUrl}`
  }

  return {
    getDistrictSlug,
    prefixPath,
    bookingPath,
    facilityDetailPath,
    loginPath
  }
}
