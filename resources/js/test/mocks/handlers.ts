import { http, HttpResponse } from 'msw';

// Mock data store for tests
const mockTokens: Array<{ id: number; name: string; abilities: string[]; created_at: string }> = [];
const mockSettings: Record<string, Record<string, unknown>> = {};

export const handlers = [
  // Settings API
  http.post('/api/settings', async ({ request }) => {
    const body = await request.json() as { key: string; value: unknown };
    const userId = '1'; // Mock user ID

    if (!mockSettings[userId]) {
      mockSettings[userId] = {};
    }
    mockSettings[userId][body.key] = body.value;

    return HttpResponse.json({
      success: true,
      key: body.key,
      value: body.value,
    });
  }),

  http.get('/api/settings/:key', ({ params }) => {
    const { key } = params;
    const userId = '1'; // Mock user ID
    const value = mockSettings[userId]?.[key as string] ?? null;

    return HttpResponse.json({
      key,
      value,
    });
  }),

  // Token APIs
  http.get('/api/tokens', () => {
    return HttpResponse.json({
      tokens: mockTokens,
    });
  }),

  http.post('/api/tokens', async ({ request }) => {
    const body = await request.json() as { name: string; abilities?: string[] };

    if (!body.name) {
      return HttpResponse.json(
        { message: 'The name field is required.', errors: { name: ['The name field is required.'] } },
        { status: 422 }
      );
    }

    const newToken = {
      id: mockTokens.length + 1,
      name: body.name,
      abilities: body.abilities || ['*'],
      created_at: new Date().toISOString(),
    };

    mockTokens.push(newToken);

    return HttpResponse.json(
      {
        token: `${newToken.id}|test-plain-text-token-${Date.now()}`,
        accessToken: newToken,
      },
      { status: 201 }
    );
  }),

  http.delete('/api/tokens/:id', ({ params }) => {
    const { id } = params;
    const index = mockTokens.findIndex((t) => t.id === Number(id));

    if (index === -1) {
      return HttpResponse.json(
        { message: 'Token not found.' },
        { status: 404 }
      );
    }

    mockTokens.splice(index, 1);

    return HttpResponse.json({ success: true });
  }),

  // Auth endpoints (for form submissions)
  http.post('/login', async ({ request }) => {
    const body = await request.json() as { email: string; password: string };

    if (!body.email || !body.password) {
      return HttpResponse.json(
        { message: 'The email field is required.', errors: { email: ['The email field is required.'] } },
        { status: 422 }
      );
    }

    // Mock successful login
    return HttpResponse.json({ success: true });
  }),

  http.post('/register', async ({ request }) => {
    const body = await request.json() as { name: string; email: string; password: string; password_confirmation: string };

    if (!body.name || !body.email || !body.password) {
      return HttpResponse.json(
        { message: 'Validation failed.', errors: {} },
        { status: 422 }
      );
    }

    // Mock successful registration
    return HttpResponse.json({ success: true });
  }),

  http.post('/logout', () => {
    return HttpResponse.json({ success: true });
  }),

  http.patch('/profile', async ({ request }) => {
    const body = await request.json() as { name?: string; email?: string };

    return HttpResponse.json({
      success: true,
      user: {
        id: 1,
        name: body.name || 'Test User',
        email: body.email || 'test@example.com',
      },
    });
  }),

  http.delete('/profile', () => {
    return HttpResponse.json({ success: true });
  }),

  // Password reset
  http.post('/forgot-password', async ({ request }) => {
    const body = await request.json() as { email: string };

    if (!body.email) {
      return HttpResponse.json(
        { message: 'The email field is required.', errors: { email: ['The email field is required.'] } },
        { status: 422 }
      );
    }

    return HttpResponse.json({
      status: 'We have emailed your password reset link!',
    });
  }),

  http.post('/reset-password', async ({ request }) => {
    const body = await request.json() as { token: string; email: string; password: string; password_confirmation: string };

    if (!body.token || !body.email || !body.password) {
      return HttpResponse.json(
        { message: 'Validation failed.', errors: {} },
        { status: 422 }
      );
    }

    return HttpResponse.json({ success: true });
  }),
];

// Helper to reset mock data between tests
export const resetMockData = () => {
  mockTokens.length = 0;
  Object.keys(mockSettings).forEach((key) => delete mockSettings[key]);
};
