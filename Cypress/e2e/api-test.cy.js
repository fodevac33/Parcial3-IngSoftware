/// <reference types="cypress" />
import fs from 'fs';

describe('API Endpoints Testing', () => {
  const baseUrl = 'http://localhost:8000/api'; 
  const resultsDir = 'cypress/fixtures/api-responses';

  before(() => {
    cy.task('fileSystem:ensureDir', resultsDir);
  });

  describe('Products Endpoints', () => {
    it('Should get all products', () => {
      cy.request({
        method: 'GET',
        url: `${baseUrl}/products`,
      }).then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.property('mensaje');
        expect(response.body).to.have.property('datos');
        
        // Save response to JSON file
        cy.task('fileSystem:writeFile', {
          filename: `${resultsDir}/all-products.json`,
          content: JSON.stringify(response.body, null, 2)
        });
      });
    });

    it('Should get a specific product', () => {
      const productId = 1; 
      cy.request({
        method: 'GET',
        url: `${baseUrl}/products/${productId}`,
      }).then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.property('mensaje');
        expect(response.body).to.have.property('datos');
        
        cy.task('fileSystem:writeFile', {
          filename: `${resultsDir}/product-${productId}.json`,
          content: JSON.stringify(response.body, null, 2)
        });
      });
    });

    it('Should create a new product', () => {
      const newProduct = {
        id: 999,
        title: 'Test Product',
        price: 99.99,
        description: 'This is a test product created by Cypress',
        category: 'test',
        image: 'https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg'
      };

      cy.request({
        method: 'POST',
        url: `${baseUrl}/products`,
        body: newProduct
      }).then((response) => {
        expect(response.status).to.eq(201);
        expect(response.body).to.have.property('mensaje');
        expect(response.body).to.have.property('datos');
        
        cy.task('fileSystem:writeFile', {
          filename: `${resultsDir}/new-product.json`,
          content: JSON.stringify(response.body, null, 2)
        });
      });
    });
  });

  // Test Carts Endpoints
  describe('Carts Endpoints', () => {
    it('Should get all carts', () => {
      cy.request({
        method: 'GET',
        url: `${baseUrl}/carts`,
      }).then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.property('mensaje');
        expect(response.body).to.have.property('datos');
        
        cy.task('fileSystem:writeFile', {
          filename: `${resultsDir}/all-carts.json`,
          content: JSON.stringify(response.body, null, 2)
        });
      });
    });

    it('Should create a new cart', () => {
      const newCart = {
        id: 999,
        userId: 1,
        products: [
          {
            productId: 1,
            quantity: 2
          },
          {
            productId: 2,
            quantity: 1
          }
        ]
      };

      cy.request({
        method: 'POST',
        url: `${baseUrl}/carts`,
        body: newCart
      }).then((response) => {
        expect(response.status).to.eq(201);
        expect(response.body).to.have.property('mensaje');
        expect(response.body).to.have.property('datos');
        
        cy.task('fileSystem:writeFile', {
          filename: `${resultsDir}/new-cart.json`,
          content: JSON.stringify(response.body, null, 2)
        });
      });
    });
  });

  // Test Users Endpoints
  describe('Users Endpoints', () => {
    it('Should get all users', () => {
      cy.request({
        method: 'GET',
        url: `${baseUrl}/users`,
      }).then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.property('mensaje');
        expect(response.body).to.have.property('datos');
        
        cy.task('fileSystem:writeFile', {
          filename: `${resultsDir}/all-users.json`,
          content: JSON.stringify(response.body, null, 2)
        });
      });
    });

    it('Should create a new user', () => {
      const randomNum = Math.floor(Math.random() * 10000);
      const newUser = {
        id: 999,
        username: `testuser${randomNum}`,
        email: `test${randomNum}@example.com`,
        password: 'password123'
      };

      cy.request({
        method: 'POST',
        url: `${baseUrl}/users`,
        body: newUser
      }).then((response) => {
        expect(response.status).to.eq(201);
        expect(response.body).to.have.property('mensaje');
        expect(response.body).to.have.property('datos');
        
        cy.task('fileSystem:writeFile', {
          filename: `${resultsDir}/new-user.json`,
          content: JSON.stringify(response.body, null, 2)
        });
      });
    });

    it('Should login with credentials', () => {
      const credentials = {
        username: 'mor_2314',
        password: '83r5^_'
      };

      cy.request({
        method: 'POST',
        url: `${baseUrl}/auth/login`,
        body: credentials
      }).then((response) => {
        expect(response.status).to.eq(200);
        expect(response.body).to.have.property('mensaje');
        expect(response.body).to.have.property('token');
        
        cy.task('fileSystem:writeFile', {
          filename: `${resultsDir}/login-response.json`,
          content: JSON.stringify(response.body, null, 2)
        });
      });
    });
  });
});