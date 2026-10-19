export interface Product {
  id: string;
  name: string;
  description: string;
  price: number;
  category: string;
  image: string;
  stock: number;
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface OrderItem {
  id: string;
  product_id: string;
  product_name: string;
  quantity: number;
  price: number;
}

export interface Order {
  id: string;
  user_email: string;
  total: number;
  status: 'pending' | 'paid' | 'shipped';
  items: OrderItem[];
  created_at: string;
  payment_method: string;
}

export interface User {
  id: string;
  name: string;
  email: string;
}
