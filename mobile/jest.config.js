module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'node',
  testMatch: ['**/__tests__/unit/**/*.test.ts'],
  // Global que o React Native define no app; sem ele, src/api/client.ts não carrega nos testes
  globals: { __DEV__: true },
};
