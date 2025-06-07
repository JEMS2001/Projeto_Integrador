---
applyTo: '**/*.ts,**/*.tsx, **/*.js, **/*.jsx, **/*.json, **/*.md'
---

> **🎯 PROMPT FRONTEND:** Este arquivo contém instruções específicas para desenvolvimento TypeScript/React.
> Para usar: `@copilot-frontend` + sua solicitação de código frontend.

# Copilot ▸ Frontend (TypeScript/React)

> **🚀 OBJETIVO:** Gerar código frontend moderno, performático e maintível seguindo as melhores práticas do ecossistema React/TypeScript.

## 1 ▪ Convenções TypeScript/React

### 1.1 Nomenclatura Específica
* **camelCase** para variáveis e funções: `const apiUrl = "..."`
* **PascalCase** para componentes: `function UserCard()`
* **SCREAMING_SNAKE_CASE** para constantes: `const API_ENDPOINTS = { ... }`
* **Prefixo `use`** para hooks: `useAuth()`, `useLocalStorage()`
* **Interfaces/Types** com sufixo Props/State/Data: `UserCardProps`, `AuthState`, `ApiResponseData`
* **Enums** com sufixo Enum: `StatusEnum`, `UserRoleEnum`

### 1.2 TypeScript Avançado
* **Evite `any`** - use generics, `unknown` ou union types
* **Props tipadas** via interfaces: `interface UserCardProps { user: User }`
* **Strict mode** ativo no tsconfig.json
* **Const assertions** para arrays/objetos imutáveis: `as const`
* **Utility types**: `Partial<T>`, `Pick<T, K>`, `Omit<T, K>` quando apropriado
* **Generic constraints**: `<T extends User>` para type safety
* **Discriminated unions** para estados complexos

### 1.3 Formatação Frontend
* **Prettier + ESLint** configurados
* Largura máxima: 100 colunas
* Sem `;` opcionais (padrão Prettier)

---

## 2 ▪ React Específico

### 2.1 Componentes
* **Apenas componentes funcionais** - zero class components
* **Hooks no topo** da função, evitar condicionais nos hooks
* **Dividir componentes** grandes (>150 linhas) em subcomponentes
* **Error boundaries** para páginas críticas e componentes de fallback
* **Prop drilling máximo de 3 níveis** - use Context ou estado global
* **Componentes puros** sempre que possível (`React.memo`)
* **Forward refs** quando necessário para bibliotecas de componentes

### 2.2 Estado e Dados
* **React Query/TanStack Query** para dados do servidor (cache, invalidação, background updates)
* **Jotai atoms** para estado global granular e reativo
* **Zustand** para estado global simples (alternativa ao Jotai)
* **Context API** apenas para dados que não mudam frequentemente
* **Estado local** (`useState`) para UI state (modals, forms, toggles)
* **useReducer** para estado complexo com múltiplas ações
* **Estado derivado** com `useMemo` ao invés de `useEffect`

### 2.3 Performance & Otimização
* **`useMemo`** para cálculos pesados (complexidade > O(n))
* **`useCallback`** para funções passadas como props ou dependencies
* **`React.memo`** para componentes puros que re-renderizam frequentemente
* **Code splitting** com `React.lazy()` e `Suspense`
* **Virtualization** (`react-window`) para listas grandes (>100 items)
* **Debounce** para inputs de busca e validações
* **Image optimization** com lazy loading e placeholders
* **Bundle analysis** regular com webpack-bundle-analyzer
* **Avoid useEffect** - prefira event handlers e estado derivado
* **Maximum 3 levels** de aninhamento por componente
---

## 3 ▪ Arquitetura Frontend

### 3.1 Estrutura de Pastas
```
src/
├─ app/              # routes (Next.js app router)
│  └─ _components/   # componentes locais da rota
├─ components/       # componentes globais reutilizáveis
│  ├─ ui/           # componentes base (Button, Input, etc.)
│  └─ features/     # componentes de domínio específico
├─ hooks/           # custom hooks reutilizáveis
├─ lib/             # configurações de bibliotecas (react-query, etc.)
├─ schemas/         # Zod validation schemas
├─ stores/          # Jotai atoms ou Zustand stores
├─ types/           # tipos TypeScript globais
├─ utils/           # utilitários e helpers
└─ constants/       # constantes da aplicação
```

### 3.2 API e Serviços
* **Axios service layer** em `/src/lib/apiClient.ts` com interceptors
* **Tipagem completa** de endpoints via interfaces
* **Error handling** centralizado com interceptors
* **Query keys** organizados por domínio em `/src/lib/queryKeys.ts`
* **API response** tipado com generics: `ApiResponse<T>`
* **Request/Response transforms** quando necessário
* **Retry logic** para requisições críticas

---

## 4 ▪ Boas Práticas Frontend

### 4.1 Code Style
* **Prefer `const`** - use `let` apenas quando reatribuir
* **Optional chaining** (`?.`) e **nullish coalescing** (`??`)
* **Destructuring** para props, objetos e arrays
* **Arrow functions** para callbacks e componentes simples
* **Early returns** para reduzir aninhamento
* **Template literals** para strings complexas
* **Object shorthand** quando possível: `{ name, age }`

### 4.2 Formulários e Validação
* **React Hook Form** + **Zod** para validação type-safe
* **Controller** para componentes controlados complexos
* **Debounce** para validações em tempo real (300ms)
* **Estados bem definidos**: loading, error, success
* **Field arrays** para listas dinâmicas
* **Schema validation** no cliente e sincronizado com backend
* **Accessible forms** com labels e ARIA attributes

### 4.3 Styling & UI
* **Tailwind CSS** preferido para styling utilitário
* **CSS Modules** ou **styled-components** quando necessário
* **Design tokens** centralizados em `/src/styles/tokens.ts`
* **Responsividade mobile-first** com breakpoints consistentes
* **Dark mode** suportado via CSS variables ou Tailwind
* **Component variants** usando bibliotecas como `class-variance-authority`
* **Accessibility** sempre: ARIA labels, keyboard navigation, contrast

---

## 5 ▪ Testes Frontend

### 5.1 Testing Strategy
* **@testing-library/react** - teste comportamento, não implementação
* **@testing-library/jest-dom** - matchers específicos para DOM
* **@testing-library/user-event** - interações realistas do usuário
* **MSW (Mock Service Worker)** - mock de APIs para testes
* **Vitest** preferido sobre Jest para novos projetos

### 5.2 O que Testar
* **Componentes críticos**: 90%+ cobertura
* **Custom hooks** isoladamente com `@testing-library/react-hooks`
* **Fluxos de usuário** principais (login, cadastro, checkout)
* **Error boundaries** e estados de fallback
* **Accessibility** com `@testing-library/jest-dom/extend-expect`
* **Integrações** entre componentes (não unitários demais)

### 5.3 Estrutura de Testes
```
src/
├─ components/
│  └─ Button/
│     ├─ Button.tsx
│     ├─ Button.test.tsx
│     └─ Button.stories.tsx
├─ hooks/
│  └─ useAuth/
│     ├─ useAuth.ts
│     └─ useAuth.test.ts
└─ __tests__/
   ├─ setup.ts
   └─ utils.tsx
```

---

## 6 ▪ Error Handling & Loading States

### 6.1 Error Boundaries
* **React Error Boundary** para captura de erros
* **Fallback components** informativos e acionáveis
* **Error reporting** para serviços como Sentry
* **Graceful degradation** quando possível

### 6.2 Loading & Async States
* **Suspense** para componentes lazy-loaded
* **Skeleton loaders** ao invés de spinners genéricos
* **Progressive loading** para dados grandes
* **Optimistic updates** para melhor UX
* **Timeout handling** para requisições longas

---

## 7 ▪ Security & Best Practices

### 7.1 Segurança Frontend
* **XSS protection** - sanitize user input
* **CSRF tokens** para formulários críticos
* **Content Security Policy** configurado
* **Environment variables** nunca expostas no bundle
* **Sensitive data** nunca no localStorage
* **Dependencies** atualizadas e auditadas regularmente

### 7.2 Performance Monitoring
* **Core Web Vitals** monitorados
* **Bundle size** acompanhado com ferramentas adequadas
* **Memory leaks** prevenidos com cleanup adequado
* **Network requests** otimizadas e cachadas

---

## 8 ▪ Exemplos de Código

### 8.1 Componente com TypeScript Avançado
```tsx
interface UserCardProps {
  user: User;
  variant?: 'default' | 'compact' | 'detailed';
  onEdit?: (userId: string) => void;
  className?: string;
}

const UserCard = memo<UserCardProps>(({ 
  user, 
  variant = 'default', 
  onEdit,
  className 
}) => {
  const handleEdit = useCallback(() => {
    onEdit?.(user.id);
  }, [user.id, onEdit]);

  const cardClasses = cn(
    'user-card',
    {
      'user-card--compact': variant === 'compact',
      'user-card--detailed': variant === 'detailed',
    },
    className
  );

  return (
    <article className={cardClasses} data-testid="user-card">
      <h3>{user.name}</h3>
      <p>{user.email}</p>
      {onEdit && (
        <Button 
          onClick={handleEdit}
          variant="outline"
          size="sm"
          aria-label={`Edit ${user.name}`}
        >
          Edit User
        </Button>
      )}
    </article>
  );
});

UserCard.displayName = 'UserCard';
```

### 8.2 Custom Hook com React Query
```tsx
interface UseUsersQueryOptions {
  page?: number;
  limit?: number;
  search?: string;
}

export function useUsersQuery(options: UseUsersQueryOptions = {}) {
  const { page = 1, limit = 10, search } = options;

  return useQuery({
    queryKey: ['users', { page, limit, search }],
    queryFn: () => apiClient.getUsers({ page, limit, search }),
    staleTime: 5 * 60 * 1000, // 5 minutes
    gcTime: 10 * 60 * 1000, // 10 minutes
    enabled: Boolean(page && limit),
  });
}
```

### 8.3 Zod Schema com React Hook Form
```tsx
const userSchema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  email: z.string().email('Invalid email format'),
  age: z.number().int().min(18, 'Must be at least 18 years old'),
  role: z.enum(['admin', 'user', 'moderator']),
});

type UserFormData = z.infer<typeof userSchema>;

export function UserForm() {
  const { 
    register, 
    handleSubmit, 
    formState: { errors, isSubmitting } 
  } = useForm<UserFormData>({
    resolver: zodResolver(userSchema),
  });

  const onSubmit: SubmitHandler<UserFormData> = async (data) => {
    try {
      await apiClient.createUser(data);
      toast.success('User created successfully');
    } catch (error) {
      toast.error('Failed to create user');
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <Input
        {...register('name')}
        placeholder="Full name"
        error={errors.name?.message}
        disabled={isSubmitting}
      />
      <Input
        {...register('email')}
        type="email"
        placeholder="Email address"
        error={errors.email?.message}
        disabled={isSubmitting}
      />
      <Button 
        type="submit" 
        loading={isSubmitting}
        className="w-full"
      >
        Create User
      </Button>
    </form>
  );
}
```

### 8.4 Estado Global com Jotai
```tsx
// stores/userStore.ts
export const currentUserAtom = atom<User | null>(null);
export const isAuthenticatedAtom = atom(
  (get) => get(currentUserAtom) !== null
);

// hooks/useAuth.ts
export function useAuth() {
  const [currentUser, setCurrentUser] = useAtom(currentUserAtom);
  const isAuthenticated = useAtomValue(isAuthenticatedAtom);

  const login = useCallback(async (credentials: LoginCredentials) => {
    const user = await apiClient.login(credentials);
    setCurrentUser(user);
    return user;
  }, [setCurrentUser]);

  const logout = useCallback(() => {
    setCurrentUser(null);
    apiClient.logout();
  }, [setCurrentUser]);

  return {
    currentUser,
    isAuthenticated,
    login,
    logout,
  };
}
```
