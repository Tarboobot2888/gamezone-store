"use client"

import { useEffect, useState } from "react"
import { usePathname, useSearchParams } from "next/navigation"
import NProgress from "nprogress"

export function ProgressBar() {
  const pathname = usePathname()
  const searchParams = useSearchParams()
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    NProgress.configure({
      showSpinner: false,
      trickleSpeed: 200,
      minimum: 0.08,
    })

    const handleStart = () => {
      setIsLoading(true)
      NProgress.start()
    }

    const handleComplete = () => {
      setIsLoading(false)
      NProgress.done()
    }

    // Start progress on route change
    handleStart()

    // Complete progress after a short delay to ensure smooth transition
    const timer = setTimeout(handleComplete, 100)

    return () => {
      clearTimeout(timer)
      handleComplete()
    }
  }, [pathname, searchParams])

  return null
}
