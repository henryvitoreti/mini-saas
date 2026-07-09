export type AppError = {
  statusCode: number|null;
  statusMessage: string|null;
  message: string|null;
};

export type ErrorContent = {
  icon: string;
  eyebrow: string;
  title: string;
  description: string;
};
