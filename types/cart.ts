export interface CartItem {
  id: number
  service_id: number
  service: {
    id: number
    name: string
    description: string
    price: number
    currency: string
    image?: string
    game: {
      id: number
      name: string
      image?: string
    }
  }
  quantity: number
  price: number
  customer_info: any
  created_at: string
}

export interface AddToCartRequest {
  service_id: number
  quantity: number
  customer_info: any
}
