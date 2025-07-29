"use client"

import { useEffect } from "react"
import { usePathname } from "next/navigation"
import NProgress from "nprogress"

export function ProgressBar() {
  const pathname = usePathname()

  useEffect(() => {
    NProgress.configure({
      showSpinner: false,
      trickleSpeed: 200,
      minimum: 0.08,
    })

    const handleStart = () => NProgress.start()
    const handleComplete = () => NProgress.done()

    // Start progress on route change
    handleStart()

    // Complete progress after a short delay to ensure smooth transition
    const timer = setTimeout(handleComplete, 100)

    return () => {
      clearTimeout(timer)
      handleComplete()
    }
  }, [pathname])

  return null
}
