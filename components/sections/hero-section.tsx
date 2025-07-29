"use client"

import { useState, useEffect } from "react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Search, Gamepad2, Zap, Shield, Star } from "lucide-react"
import { motion } from "framer-motion"

const heroFeatures = [
  { icon: Zap, text: "Instant Delivery" },
  { icon: Shield, text: "Secure Payments" },
  { icon: Star, text: "24/7 Support" },
]

const popularGames = ["PUBG Mobile", "Free Fire", "Mobile Legends", "Call of Duty Mobile", "Genshin Impact", "Valorant"]

export function HeroSection() {
  const [searchQuery, setSearchQuery] = useState("")
  const [currentGameIndex, setCurrentGameIndex] = useState(0)

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentGameIndex((prev) => (prev + 1) % popularGames.length)
    }, 3000)

    return () => clearInterval(interval)
  }, [])

  return (
    <section className="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-gaming-dark via-purple-900 to-gaming-primary">
      {/* Animated background elements */}
      <div className="absolute inset-0">
        <div className="absolute top-20 left-20 w-72 h-72 bg-gaming-accent/20 rounded-full blur-3xl animate-pulse" />
        <div className="absolute bottom-20 right-20 w-96 h-96 bg-gaming-secondary/20 rounded-full blur-3xl animate-pulse delay-1000" />
        <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gaming-primary/10 rounded-full blur-3xl animate-pulse delay-2000" />
      </div>

      {/* Grid pattern overlay */}
      <div className="absolute inset-0 bg-[url('/grid-pattern.svg')] opacity-10" />

      <div className="container mx-auto px-4 relative z-10">
        <div className="text-center max-w-4xl mx-auto">
          {/* Main heading */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="mb-8"
          >
            <Badge
              variant="secondary"
              className="mb-4 px-4 py-2 text-sm font-medium bg-white/10 text-white border-white/20"
            >
              <Gamepad2 className="w-4 h-4 mr-2" />
              #1 Gaming Marketplace
            </Badge>

            <h1 className="text-5xl md:text-7xl font-bold font-gaming mb-6 bg-gradient-to-r from-white via-gaming-accent to-gaming-secondary bg-clip-text text-transparent">
              Level Up Your
              <br />
              <span className="relative">
                Gaming Experience
                <div className="absolute -bottom-2 left-0 right-0 h-1 bg-gradient-to-r from-gaming-accent to-gaming-secondary rounded-full" />
              </span>
            </h1>

            <p className="text-xl md:text-2xl text-gray-300 mb-8 leading-relaxed">
              Get instant top-ups, premium accounts, and exclusive gaming services for{" "}
              <span className="text-gaming-accent font-semibold animate-pulse">{popularGames[currentGameIndex]}</span>{" "}
              and more
            </p>
          </motion.div>

          {/* Search bar */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="mb-12"
          >
            <div className="relative max-w-2xl mx-auto">
              <div className="relative">
                <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                <Input
                  type="text"
                  placeholder="Search for games, services, or accounts..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-12 pr-32 py-4 text-lg bg-white/10 border-white/20 text-white placeholder:text-gray-400 focus:bg-white/20 focus:border-gaming-accent transition-all duration-300"
                />
                <Button
                  size="lg"
                  className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gaming-accent hover:bg-gaming-accent/90 text-white px-6"
                >
                  Search
                </Button>
              </div>
            </div>
          </motion.div>

          {/* CTA Buttons */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            className="flex flex-col sm:flex-row gap-4 justify-center mb-16"
          >
            <Button
              asChild
              size="lg"
              className="bg-gaming-primary hover:bg-gaming-primary/90 text-white px-8 py-4 text-lg font-semibold gaming-glow"
            >
              <Link href="/shop">
                Browse Games
                <Gamepad2 className="ml-2 w-5 h-5" />
              </Link>
            </Button>

            <Button
              asChild
              variant="outline"
              size="lg"
              className="border-white/30 text-white hover:bg-white/10 px-8 py-4 text-lg font-semibold bg-transparent"
            >
              <Link href="/accounts">View Accounts</Link>
            </Button>
          </motion.div>

          {/* Features */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.6 }}
            className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-3xl mx-auto"
          >
            {heroFeatures.map((feature, index) => (
              <div
                key={index}
                className="flex items-center justify-center space-x-3 text-white/90 hover:text-white transition-colors duration-300"
              >
                <div className="p-2 bg-white/10 rounded-full">
                  <feature.icon className="w-6 h-6" />
                </div>
                <span className="font-medium text-lg">{feature.text}</span>
              </div>
            ))}
          </motion.div>

          {/* Stats */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.8 }}
            className="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto"
          >
            {[
              { number: "50K+", label: "Happy Customers" },
              { number: "100+", label: "Games Supported" },
              { number: "24/7", label: "Support Available" },
              { number: "99.9%", label: "Uptime Guarantee" },
            ].map((stat, index) => (
              <div key={index} className="text-center">
                <div className="text-3xl md:text-4xl font-bold font-gaming text-gaming-accent mb-2">{stat.number}</div>
                <div className="text-gray-300 text-sm md:text-base">{stat.label}</div>
              </div>
            ))}
          </motion.div>
        </div>
      </div>

      {/* Scroll indicator */}
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 1, delay: 1 }}
        className="absolute bottom-8 left-1/2 transform -translate-x-1/2"
      >
        <div className="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center">
          <div className="w-1 h-3 bg-white/60 rounded-full mt-2 animate-bounce" />
        </div>
      </motion.div>
    </section>
  )
}
