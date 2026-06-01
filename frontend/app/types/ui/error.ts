export type AppError = {
  statusCode?: number;
  statusMessage?: string;
  message?: string;
};

export type ErrorContent = {
  icon: string;
  eyebrow: string;
  title: string;
  description: string;
};
