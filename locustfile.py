from locust import HttpUser, task, between

class APIUser(HttpUser):
    wait_time = between(1, 5)  
    
    
    @task(3)  
    def get_all_products(self):
        self.client.get("/products")
    
    @task(2)
    def get_single_product(self):
        self.client.get("/products/1")
    
    @task(1)  
    def create_product(self):
        payload = {
            "title": "Test Product",
            "price": 13.5,
            "description": "Lorem ipsum set",
            "image": "https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg",
            "category": "test"
        }
        self.client.post("/products", json=payload)
    
    @task(1)
    def update_product(self):
        payload = {
            "title": "Updated Test Product",
            "price": 15.99
        }
        self.client.put("/products/1", json=payload)
    
    @task(1)
    def delete_product(self):
        self.client.delete("/products/1")