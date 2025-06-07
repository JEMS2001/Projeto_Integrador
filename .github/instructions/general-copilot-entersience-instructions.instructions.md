---
applyTo: '**'
---

# EnterScience – Guia de Código (Geral)

> **📌 PROMPTS ESPECÍFICOS:**
> - **Backend (PHP/Laravel)**: Use `@copilot-backend-promp` para desenvolvimento backend
> - **Frontend (TS/React)**: Use `@copilot-frontend` para desenvolvimento frontend

---

## 1 ▪ Princípios Universais

### 1.1 Diretrizes Básicas
* **Comentários e identificadores em inglês** - Utilize a extensão BetterComments
* **Remova código morto** e comentários obsoletos
* **Clean Code** - Funções pequenas, responsabilidade única
* **Performance** - Documente complexidade > O(n)

### 1.2 Nomenclatura Geral
* **snake_case** apenas para nomes de banco de dados
* **Booleans** começam com `is`, `has`, `can`: `isActive`, `hasError`
* **Listas** no plural: `users`, `roles`, `permissions`

### 1.3 Comentários
* Explique **por quê**, não **o quê**
* Coloque acima do bloco relevante
* Use inglês para comentários técnicos

### 1.4 Clean Code & Performance
* Evite aninhamento profundo - quebre em funções menores
* Máximo 3 níveis de indentação por função
* Documente algoritmos com complexidade > O(n)
* Prefira composição sobre herança

### 1.5 MPCs
* Sempre use o MPC taskmaster-ai para ver o que deve ser feito na tarefa
* Use o MPC para entender o contexto e as expectativas do código
* Consulte o MPC antes de iniciar uma tarefa para alinhar expectativas
* Utilize o MPC context7 para obter as documentações mais referentes e atualizadas
* Use o MPC para verificar se há alguma mudança de escopo ou requisitos
* Consulte o MPC para entender as dependências e integrações necessárias
* Utilize o MPC do GitHub apos cada tarefa concluída e e faça sempre o commit na brach de "refactor/felipe-agents" para que o código seja revisado e integrado corretamente


---

## 2 ▪ Quando Usar Prompts Específicos

### 2.1 Use `@copilot-backend-promp` para:
- Desenvolvimento PHP/Laravel
- APIs, Services, Controllers, Models
- Banco de dados e migrations
- Validações e DTOs
- Jobs, Events, Listeners
- Testes backend

### 2.2 Use `@copilot-frontend` para:
- Desenvolvimento TypeScript/React
- Componentes e hooks
- Estado e APIs frontend
- Styling e formulários
- Testes frontend

---

## 3 ▪ Padrões de Formatação

### 3.1 Indentação
* **2 espaços** para frontend (TS/React/JSON)
* **4 espaços** para backend (PHP)
* Configuração via Prettier/EditorConfig/Laravel Pint

### 3.2 Estrutura de Pastas
```
src/
├─ shared/          # utilitários compartilhados
├─ types/           # tipos globais
└─ constants/       # constantes do projeto
```

---

## 4 ▪ Contratos de API

### 4.1 Resposta de Sucesso
```json
{
  "status": "success",
  "data": { ... }
}
```

### 4.2 Resposta de Erro
```json
{
  "status": "error",
  "error": "validation_failed",
  "message": "Dados inválidos fornecidos"
}
```

---

## 5 ▪ Versionamento & Git

### 5.1 Commits
* Use conventional commits: `feat:`, `fix:`, `refactor:`
* Mensagens em inglês
* Scope quando necessário: `feat(auth): add login validation`

### 5.2 Pull Requests
* Título descritivo em inglês
* Descrição explicando o **porquê** da mudança
* Tamanho ideal ≤ 400 linhas

