import { HeroSection } from "@/components/sections/hero-section"
import { FeaturedGames } from "@/components/sections/featured-games"
import { PopularServices } from "@/components/sections/popular-services"
import { FeaturedAccounts } from "@/components/sections/featured-accounts"
import { StatsSection } from "@/components/sections/stats-section"
import { TestimonialsSection } from "@/components/sections/testimonials-section"
import { NewsletterSection } from "@/components/sections/newsletter-section"
import { Navbar } from "@/components/layout/navbar"
import { Footer } from "@/components/layout/footer"

export default function HomePage() {
  return (
    <>
      <Navbar />
      <main className="flex-1">
        <HeroSection />
        <FeaturedGames />
        <PopularServices />
        <FeaturedAccounts />
        <StatsSection />
        <TestimonialsSection />
        <NewsletterSection />
      </main>
      <Footer />
    </>
  )
}
