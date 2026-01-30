import { RegisterForm } from '@/components/RegisterForm';

export const metadata = {
  title: 'Registrarse - Pokemon BFF',
  description: 'Crea una nueva cuenta en Pokemon BFF',
};

export default function RegisterPage() {
  return (
    <div className="container mx-auto px-4 py-12">
      <div className="max-w-md mx-auto">
        <RegisterForm />
      </div>
    </div>
  );
}
