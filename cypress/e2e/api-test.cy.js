/// <reference types="cypress" />
import fs from 'fs';

describe('API Endpoints Testing', () => {
  const baseUrl = 'https://fakestoreapi.com'; 
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

});