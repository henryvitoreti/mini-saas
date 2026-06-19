export type AuthenticatedUser = {
  id: number;
  role_id: number;
  name: string;
  email: string;
  phone: string|null;
  is_active: boolean;
  permissions?: string[];
};

export type LoginResponse = {
  token_type: string;
  expires_in: number;
  access_token: string;
  user: AuthenticatedUser;
};

export type LoginPayload = {
  email: string;
  password: string;
  remember_login: boolean;
};
