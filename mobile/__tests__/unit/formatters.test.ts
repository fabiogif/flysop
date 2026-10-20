describe('Utilitários e Validadores Mobile', () => {
  it('valida limites de latitude entre -90 e 90', () => {
    const isValidLat = (lat: number) => lat >= -90 && lat <= 90;
    expect(isValidLat(-12.9714)).toBe(true);
    expect(isValidLat(0)).toBe(true);
    expect(isValidLat(90.1)).toBe(false);
    expect(isValidLat(-91.0)).toBe(false);
  });

  it('valida limites de longitude entre -180 e 180', () => {
    const isValidLng = (lng: number) => lng >= -180 && lng <= 180;
    expect(isValidLng(-38.5014)).toBe(true);
    expect(isValidLng(180)).toBe(true);
    expect(isValidLng(-181.0)).toBe(false);
    expect(isValidLng(185.0)).toBe(false);
  });

  it('calcula caracteres restantes da bio com limite de 160', () => {
    const limit = 160;
    const bioText = 'Líder comunitário no bairro Centro.';
    const remaining = limit - bioText.length;
    expect(remaining).toBe(125);
    expect(remaining).toBeGreaterThanOrEqual(0);
  });

  it('avalia força de senha corretamente', () => {
    const evaluateStrength = (pass: string) => {
      const hasMinLength = pass.length >= 8;
      const hasLetter = /[a-zA-Z]/.test(pass);
      const hasNumber = /[0-9]/.test(pass);
      return [hasMinLength, hasLetter, hasNumber].filter(Boolean).length;
    };

    expect(evaluateStrength('12345')).toBe(1); // apenas minlength false, letter false, number true -> 1
    expect(evaluateStrength('senhafraca')).toBe(2); // minlength true, letter true, number false -> 2
    expect(evaluateStrength('SenhaForte123')).toBe(3); // todas verdadeiras -> 3
  });
});
