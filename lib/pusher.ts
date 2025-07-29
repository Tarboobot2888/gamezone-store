import Pusher from "pusher-js"

class PusherService {
  private pusher: Pusher | null = null
  private channels: Map<string, any> = new Map()

  constructor() {
    this.initializePusher()
  }

  private initializePusher() {
    if (typeof window !== "undefined") {
      this.pusher = new Pusher(process.env.NEXT_PUBLIC_PUSHER_APP_KEY!, {
        cluster: process.env.NEXT_PUBLIC_PUSHER_APP_CLUSTER!,
        encrypted: true,
        authEndpoint: `${process.env.NEXT_PUBLIC_API_URL}/broadcasting/auth`,
        auth: {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          },
        },
      })
    }
  }

  subscribe(channelName: string) {
    if (!this.pusher) return null

    if (this.channels.has(channelName)) {
      return this.channels.get(channelName)
    }

    const channel = this.pusher.subscribe(channelName)
    this.channels.set(channelName, channel)
    return channel
  }

  unsubscribe(channelName: string) {
    if (!this.pusher) return

    if (this.channels.has(channelName)) {
      this.pusher.unsubscribe(channelName)
      this.channels.delete(channelName)
    }
  }

  disconnect() {
    if (this.pusher) {
      this.pusher.disconnect()
      this.channels.clear()
    }
  }
}

export const pusherService = new PusherService()
