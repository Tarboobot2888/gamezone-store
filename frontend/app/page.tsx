import { Suspense } from "react"
import { HeroSection } from "@/components/sections/hero-section"
import { FeaturedGames } from "@/components/sections/featured-games"
import { FeaturedAccounts } from "@/components/sections/featured-accounts"
import { StatsSection } from "@/components/sections/stats-section"
import { TestimonialsSection } from "@/components/sections/testimonials-section"
import { Navbar } from "@/components/layout/navbar"
import { Footer } from "@/components/layout/footer"
import { LoadingSpinner } from "@/components/ui/loading-spinner"

export default function HomePage() {
  return (
    <div className="min-h-screen bg-background">
      <Navbar />

      <main>
        <HeroSection />

        <Suspense fallback={<LoadingSpinner />}>
          <FeaturedGames />
        </Suspense>

        <Suspense fallback={<LoadingSpinner />}>
          <FeaturedAccounts />
        </Suspense>

        <StatsSection />
        <TestimonialsSection />
      </main>

      <Footer />
    </div>
  )
}
