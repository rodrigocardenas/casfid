import { LoginForm } from '@/components/LoginForm';

export const metadata = {
  title: 'Iniciar Sesión - Pokemon BFF',
  description: 'Inicia sesión en tu cuenta de Pokemon BFF',
};

export default function LoginPage() {
  return (
    <div className="container mx-auto px-4 py-12">
      <div className="max-w-md mx-auto">
        <LoginForm />
      </div>
    </div>
  );
}
