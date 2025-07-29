import type React from "react"
import type { Metadata } from "next"
import { Inter } from "next/font/google"
import "./globals.css"
import { ThemeProvider } from "@/components/theme-provider"
import { AuthProvider } from "@/components/auth-provider"
import { CartProvider } from "@/components/cart-provider"
import { Toaster } from "@/components/ui/sonner"
import { ProgressBar } from "@/components/progress-bar"

const inter = Inter({ subsets: ["latin"] })

export const metadata: Metadata = {
  title: "GameZone - Gaming Marketplace",
  description: "Professional multi-language gaming marketplace and top-up platform",
  keywords: ["gaming", "marketplace", "top-up", "game accounts", "PUBG", "Free Fire"],
  authors: [{ name: "GameZone Team" }],
  openGraph: {
    title: "GameZone - Gaming Marketplace",
    description: "Professional multi-language gaming marketplace and top-up platform",
    type: "website",
    locale: "en_US",
    alternateLocale: "ar_SA",
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body className={inter.className}>
        <ThemeProvider attribute="class" defaultTheme="dark" enableSystem disableTransitionOnChange>
          <AuthProvider>
            <CartProvider>
              <ProgressBar />
              {children}
              <Toaster />
            </CartProvider>
          </AuthProvider>
        </ThemeProvider>
      </body>
    </html>
  )
}
