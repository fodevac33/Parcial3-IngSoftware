from locust import HttpUser, task, between
import json
import random

class FakeStoreAPIUser(HttpUser):
    wait_time = between(1, 3)  # Tiempo entre solicitudes (1-5 segundos)
    
    # Datos de prueba
    test_user = {
        "id": 999,
        "username": "testuser",
        "email": "test@example.com",
        "password": "password123"
    }
    
    test_product = {
        "id": 999,
        "title": "Producto de prueba",
        "price": 99.99,
        "description": "Este es un producto de prueba para Locust",
        "category": "test",
        "image": "https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg"
    }
    
    test_cart = {
        "id": 999,
        "userId": 999,
        "products": [
            {
                "productId": 1,
                "quantity": 2
            },
            {
                "productId": 2,
                "quantity": 1
            }
        ]
    }
    
    product_ids = []
    cart_ids = []
    user_ids = []
    
    def on_start(self):
        with self.client.get("/product", catch_response=True) as response:
            if response.status_code == 200:
                data = response.json()
                if isinstance(data, dict) and 'datos' in data:
                    products = data['datos']
                else:
                    products = data
                
                self.product_ids = [product['id'] for product in products if isinstance(product, dict) and 'id' in product][:5]  # Limitar a 5 productos
    
    # TAREAS PARA PRODUCTOS
    
    @task(4)
    def get_all_products(self):
        with self.client.get("/product", catch_response=True) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Error al obtener productos: {response.status_code}")
    
    @task(5)
    def get_single_product(self):
        if self.product_ids:
            product_id = random.choice(self.product_ids)
            with self.client.get(f"/product/{product_id}", catch_response=True) as response:
                if response.status_code == 200:
                    response.success()
                else:
                    response.failure(f"Error al obtener producto {product_id}: {response.status_code}")
    
    @task(2)
    def create_product(self):
        with self.client.post("/product", json=self.test_product, catch_response=True) as response:
            if response.status_code in [200, 201]:
                response.success()
                # Guardar el ID si es posible
                try:
                    data = response.json()
                    if isinstance(data, dict) and 'datos' in data and 'id' in data['datos']:
                        self.product_ids.append(data['datos']['id'])
                except:
                    pass
            else:
                response.failure(f"Error al crear producto: {response.status_code}")
    
    @task(1)
    def update_product(self):
        if self.product_ids:
            product_id = random.choice(self.product_ids)
            updated_product = {
                "title": f"Producto actualizado {random.randint(1, 1000)}",
                "price": random.uniform(10, 200)
            }
            with self.client.put(f"/product/{product_id}", json=updated_product, catch_response=True) as response:
                if response.status_code == 200:
                    response.success()
                else:
                    response.failure(f"Error al actualizar producto {product_id}: {response.status_code}")
    
    @task(1)
    def delete_product(self):
        if self.product_ids:
            product_id = random.choice(self.product_ids)
            with self.client.delete(f"/product/{product_id}", catch_response=True) as response:
                if response.status_code in [200, 204]:
                    response.success()
                    # Eliminar el ID de la lista
                    if product_id in self.product_ids:
                        self.product_ids.remove(product_id)
                else:
                    response.failure(f"Error al eliminar producto {product_id}: {response.status_code}")
    
    # TAREAS PARA CARRITOS
    
    @task(5)
    def get_all_carts(self):
        with self.client.get("/carts", catch_response=True) as response:
            if response.status_code == 200:
                response.success()
                # Guardar IDs de carritos
                try:
                    data = response.json()
                    if isinstance(data, dict) and 'datos' in data:
                        carts = data['datos']
                        self.cart_ids = [cart['id'] for cart in carts if isinstance(cart, dict) and 'id' in cart][:3]  # Limitar a 3 carritos
                except:
                    pass
            else:
                response.failure(f"Error al obtener carritos: {response.status_code}")
    
    @task(3)
    def get_single_cart(self):
        if self.cart_ids:
            cart_id = random.choice(self.cart_ids)
            with self.client.get(f"/carts/{cart_id}", catch_response=True) as response:
                if response.status_code == 200:
                    response.success()
                else:
                    response.failure(f"Error al obtener carrito {cart_id}: {response.status_code}")
    
    @task(2)
    def create_cart(self):
        test_cart = self.test_cart.copy()
        test_cart["id"] = random.randint(1000, 9999)
        
        with self.client.post("/carts", json=test_cart, catch_response=True) as response:
            if response.status_code in [200, 201]:
                response.success()
                # Guardar el ID si es posible
                try:
                    data = response.json()
                    if isinstance(data, dict) and 'datos' in data and 'id' in data['datos']:
                        self.cart_ids.append(data['datos']['id'])
                except:
                    pass
            else:
                response.failure(f"Error al crear carrito: {response.status_code}")
    
    @task(1)
    def add_products_to_cart(self):
        if self.cart_ids and self.product_ids:
            cart_id = random.choice(self.cart_ids)
            # Seleccionar un producto aleatorio
            product_id = random.choice(self.product_ids)
            
            new_products = {
                "products": [
                    {
                        "productId": product_id,
                        "quantity": random.randint(1, 5)
                    }
                ]
            }
            
            with self.client.post(f"/carts/{cart_id}/product", json=new_products, catch_response=True) as response:
                if response.status_code == 200:
                    response.success()
                else:
                    response.failure(f"Error al añadir productos al carrito {cart_id}: {response.status_code}")
    
    @task(1)
    def update_cart(self):
        if self.cart_ids and self.product_ids:
            cart_id = random.choice(self.cart_ids)
            
            updated_cart = {
                "products": [
                    {
                        "productId": random.choice(self.product_ids),
                        "quantity": random.randint(1, 3)
                    }
                ]
            }
            
            with self.client.put(f"/carts/{cart_id}", json=updated_cart, catch_response=True) as response:
                if response.status_code == 200:
                    response.success()
                else:
                    response.failure(f"Error al actualizar carrito {cart_id}: {response.status_code}")
    
    # TAREAS PARA USUARIOS
    
    @task(5)
    def get_all_users(self):
        with self.client.get("/users", catch_response=True) as response:
            if response.status_code == 200:
                response.success()
                # Guardar IDs de usuarios
                try:
                    data = response.json()
                    if isinstance(data, dict) and 'datos' in data:
                        users = data['datos']
                        self.user_ids = [user['id'] for user in users if isinstance(user, dict) and 'id' in user][:3]  # Limitar a 3 usuarios
                except:
                    pass
            else:
                response.failure(f"Error al obtener usuarios: {response.status_code}")
    
    @task(3)
    def get_single_user(self):
        if self.user_ids:
            user_id = random.choice(self.user_ids)
            with self.client.get(f"/users/{user_id}", catch_response=True) as response:
                if response.status_code == 200:
                    response.success()
                else:
                    response.failure(f"Error al obtener usuario {user_id}: {response.status_code}")
    
    @task(2)
    def create_user(self):
        # Modificar el usuario de prueba con datos aleatorios
        test_user = self.test_user.copy()
        random_num = random.randint(1000, 9999)
        test_user["id"] = random_num
        test_user["username"] = f"testuser{random_num}"
        test_user["email"] = f"test{random_num}@example.com"
        
        with self.client.post("/users", json=test_user, catch_response=True) as response:
            if response.status_code in [200, 201]:
                response.success()
                # Guardar el ID si es posible
                try:
                    data = response.json()
                    if isinstance(data, dict) and 'datos' in data and 'id' in data['datos']:
                        self.user_ids.append(data['datos']['id'])
                except:
                    pass
            else:
                response.failure(f"Error al crear usuario: {response.status_code}")
    
    @task(1)
    def login_user(self):
        credentials = {
            "username": "mor_2314",  # Usuario de ejemplo de FakeStoreAPI
            "password": "83r5^_"
        }
        
        with self.client.post("/auth/login", json=credentials, catch_response=True) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Error al iniciar sesión: {response.status_code}")