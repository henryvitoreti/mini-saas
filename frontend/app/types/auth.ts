export type AuthenticatedPermission = {
  id: number;
  name: string;
  slug: string;
  base_front_url: string;
  base_api_url: string;
  show_locked_routes: boolean;
  is_active: boolean;
};

export type AuthenticatedRole = {
  id: number;
  name: string;
  slug: string;
  description: string;
  is_active: boolean;
};

export type AuthenticatedCompany = {
  id: number;
  role_id: number|null;
  name: string;
  document: string;
  email: string;
  phone: string;
  secondary_phone: string|null;
  zip_code: string|null;
  street: string|null;
  number: string|null;
  complement: string|null;
  district: string|null;
  city: string|null;
  state: string|null;
  logo_path: string|null;
  notes: string|null;
  role: AuthenticatedRole|null;
};

export type AuthenticatedUser = {
  id: number;
  name: string;
  email: string;
  phone: string|null;
  is_active: boolean;
};

export type AuthenticatedSession = {
  user: AuthenticatedUser;
  company: AuthenticatedCompany|null;
};

export type AuthenticatedPermissions = AuthenticatedPermission[];

export type AuthenticatedContext = AuthenticatedSession & { permissions: AuthenticatedPermissions; };

export type LoginResponse = AuthenticatedContext & {
  token_type: string;
  expires_in: number;
  access_token: string;
};

export type LoginPayload = {
  email: string;
  password: string;
  remember_login: boolean;
};
