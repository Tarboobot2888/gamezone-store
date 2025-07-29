"use client"

import { motion } from "framer-motion"
import { Button } from "@/components/ui/button"
import { ArrowRight, Gamepad2, Shield, Zap } from "lucide-react"
import Link from "next/link"

export function HeroSection() {
  return (
    <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
      {/* Background gradient */}
      <div className="absolute inset-0 gaming-gradient opacity-10" />

      {/* Animated background elements */}
      <div className="absolute inset-0">
        {[...Array(20)].map((_, i) => (
          <motion.div
            key={i}
            className="absolute w-2 h-2 bg-gaming-primary/20 rounded-full"
            style={{
              left: `${Math.random() * 100}%`,
              top: `${Math.random() * 100}%`,
            }}
            animate={{
              y: [0, -20, 0],
              opacity: [0.2, 0.8, 0.2],
            }}
            transition={{
              duration: 3 + Math.random() * 2,
              repeat: Number.POSITIVE_INFINITY,
              delay: Math.random() * 2,
            }}
          />
        ))}
      </div>

      <div className="container mx-auto px-4 relative z-10">
        <div className="text-center max-w-4xl mx-auto">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="mb-8"
          >
            <h1 className="text-5xl md:text-7xl font-bold mb-6 bg-gradient-to-r from-gaming-primary via-gaming-secondary to-gaming-accent bg-clip-text text-transparent">
              GameZone
            </h1>
            <p className="text-xl md:text-2xl text-muted-foreground mb-8">Your Ultimate Gaming Marketplace</p>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Discover premium game accounts, instant top-ups, and exclusive gaming services. Join thousands of gamers
              who trust GameZone for their gaming needs.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="flex flex-col sm:flex-row gap-4 justify-center mb-12"
          >
            <Button asChild size="lg" className="gaming-button">
              <Link href="/shop">
                Explore Games <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
            </Button>
            <Button asChild variant="outline" size="lg">
              <Link href="/topup">Quick Top-up</Link>
            </Button>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-3xl mx-auto"
          >
            <div className="gaming-card p-6 text-center">
              <Gamepad2 className="h-12 w-12 text-gaming-primary mx-auto mb-4" />
              <h3 className="text-lg font-semibold mb-2">Premium Accounts</h3>
              <p className="text-muted-foreground">High-level accounts with rare items and achievements</p>
            </div>

            <div className="gaming-card p-6 text-center">
              <Zap className="h-12 w-12 text-gaming-secondary mx-auto mb-4" />
              <h3 className="text-lg font-semibold mb-2">Instant Delivery</h3>
              <p className="text-muted-foreground">Fast and secure delivery within minutes of purchase</p>
            </div>

            <div className="gaming-card p-6 text-center">
              <Shield className="h-12 w-12 text-gaming-accent mx-auto mb-4" />
              <h3 className="text-lg font-semibold mb-2">Secure & Safe</h3>
              <p className="text-muted-foreground">Protected transactions with 24/7 customer support</p>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  )
}
