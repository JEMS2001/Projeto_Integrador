---
mode: 'agent'
tools: ['githubRepo', 'codebase']
description: 'Prompt para auxiliar na geração de código **frontend** moderno em TypeScript/React, com foco em performance, acessibilidade e manutenibilidade.'
--- 
name: 'Copilot Frontend Prompt'

> **📋 COMO USAR:** Digite `@copilot-frontend` seguido da sua solicitação para desenvolvimento TypeScript/React.
> **🎯 APLICA-SE A:** Componentes, hooks, stores, pages, styling, formulários, validações, testes.
> **🚀 OBJETIVO:** Código frontend type-safe, performático e acessível.

## 1. **Arquitetura & Estrutura**
```
src/
├─ app/             # App Router (Next.js) ou routes
│  └─ _components/  # Componentes locais da rota
├─ components/      # Componentes globais reutilizáveis
│  ├─ ui/          # Componentes base (Button, Input, Card)
│  └─ features/    # Componentes de domínio específico
├─ hooks/          # Custom hooks
├─ lib/            # Configurações de bibliotecas
├─ schemas/        # Zod validation schemas
├─ stores/         # Jotai atoms ou Zustand stores
├─ types/          # TypeScript types/interfaces globais
├─ utils/          # Helpers e utilitários
└─ constants/      # Constantes da aplicação
```

2. **TypeScript & Code Quality**  
- **Strict TypeScript**: `strict: true`, evite `any` - use generics, `unknown`, union types
- **Prettier + ESLint**: Indentação 2 espaços, largura máx. 100 colunas
- **Nomenclatura**: camelCase (variáveis), PascalCase (componentes), SCREAMING_SNAKE_CASE (constantes)
- **Types**: Interfaces para props (`UserCardProps`), utility types (`Partial<T>`, `Pick<T, K>`)
- **Comentários**: Em inglês, explique *por que*, não *o que*

3. **React Moderno & Performance**  
- **Componentes funcionais** apenas - zero class components
- **Hooks no topo**, sem condicionais nos hooks
- **Code splitting**: `React.lazy()` + `Suspense` para componentes grandes
- **Memoization**: `React.memo`, `useMemo`, `useCallback` quando apropriado
- **Evite useEffect**: Prefira event handlers e estado derivado
- **Máximo 3 níveis** de aninhamento por componente
- **150 LOC máximo** por componente - quebrar em subcomponentes

4. **Estado & Dados**
- **React Query/TanStack Query**: Cache e sincronização de dados do servidor
- **Jotai**: Estado global atômico e granular (`atom`, `useAtom`)
- **Zustand**: Alternativa simples para estado global
- **useState**: Apenas para UI state local (modals, toggles)
- **useReducer**: Para estado complexo com múltiplas ações
- **Context API**: Apenas para dados que mudam raramente

5. **UI, Styling & Acessibilidade**  
- **Tailwind CSS**: Preferido para styling utilitário (`className="flex items-center space-x-2"`)
- **shadcn/ui**: Componentes headless com Tailwind (preferido para novos projetos)
- **CSS Modules/SCSS**: Para componentes específicos quando necessário
- **styled-components**: Apenas se já configurado no projeto
- **Design System**: Tokens centralizados, variantes de componentes
- **Responsividade**: Mobile-first, breakpoints consistentes
- **Acessibilidade**: ARIA labels, keyboard navigation, contrast ratios
- **Dark mode**: Suporte via CSS variables ou Tailwind

6. **Formulários & Validação**
- **React Hook Form**: Performance e DX superiores (`useForm`, `Controller`)
- **Zod**: Validação type-safe de schemas (`z.object()`, `z.infer<>`)
- **Debounce**: 300ms para validações em tempo real
- **Estados**: loading, error, success bem definidos
- **Accessibility**: Labels apropriados, error announcements

7. **API & Data Fetching**
- **Axios**: Com interceptors para auth/error handling
- **React Query**: Cache inteligente, background updates, invalidation
- **Error boundaries**: Captura de erros em componentes críticos
- **Loading states**: Skeleton loaders > spinners genéricos
- **Optimistic updates**: Para melhor UX em mutações
8. **Testes & Qualidade**  
- **Vitest**: Preferido para novos projetos (performance superior)
- **@testing-library/react**: Teste comportamento, não implementação
- **MSW**: Mock de APIs para testes realistas
- **@testing-library/user-event**: Interações autênticas do usuário
- **Cobertura mínima**: 80% para componentes críticos
- **Test hooks**: Isoladamente com `@testing-library/react-hooks`

9. **Performance & Otimização**  
- **Code splitting**: `React.lazy()` + `Suspense`
- **Virtualização**: `@tanstack/react-virtual` para listas grandes (>100 items)
- **Bundle analysis**: Monitoramento regular do tamanho
- **Memory leaks**: Cleanup adequado de listeners e timers
- **Core Web Vitals**: LCP, FID, CLS otimizados
- **Image optimization**: Lazy loading, WebP, placeholders

10. **Segurança & Deploy**
- **XSS protection**: Sanitização de inputs do usuário
- **Environment variables**: Nunca exponha segredos no bundle
- **CSP**: Content Security Policy configurado
- **Dependencies**: Audit regular com `npm audit`
- **Error monitoring**: Sentry ou similar configurado

### 🎯 Como Agir
- **Detecção automática**: Verificar package.json/imports antes de sugerir bibliotecas
- **Context-aware**: Adaptar sugestões ao stack existente
- **Best practices first**: Sempre priorizar segurança, performance e acessibilidade
- **Modern patterns**: Usar as práticas mais recentes do ecossistema React
- **Fallback Strategy**: Se Tailwind não detectado, use Bootstrap; se Bootstrap não detectado, use CSS vanilla ou SCSS.
- **Library-Specific Examples**: Ao usar uma biblioteca específica, inclua imports e uso correto
- Antes de gerar código, verifique se já existe componente/hook similar – não duplique.  
- Proponha código **apenas** como diff ou arquivo completo atualizado.  
- Se perceber violação das regras, sugira/refatore antes de avançar.  
- **Especifique dependências**: Sempre mencione quais packages instalar (`bun add react-hot-toast`)
- Responda com código ou instruções técnicas concisas; evite explicações verbosas.

### Exemplo de Detecção de Context
```typescript
// Se detectar Tailwind
<button className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">

// Se detectar Bootstrap  
<button className="btn btn-primary">

// Se detectar MUI
<Button variant="contained" color="primary">

// Se detectar shadcn/ui
<Button variant="default" size="md">

// Se detectar SCSS
<button className={styles.primaryButton}>
```

### ⚡ Prioridades de Implementação
1. **Type safety** - TypeScript strict, props tipadas
2. **Performance** - Lazy loading, memoization apropriada  
3. **Accessibility** - ARIA, keyboard navigation
4. **User Experience** - Loading states, error handling
5. **Maintainability** - Componentes pequenos, testes adequados

### 🔧 Workflow de Desenvolvimento
- **Análise primeiro**: Entender context e stack existente
- **Incrementalidade**: Melhorias graduais vs reescrita completa
- **Compatibilidade**: Manter consistência com código existente
- **Documentação**: README atualizado e exemplos práticos
