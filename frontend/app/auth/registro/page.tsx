'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'

interface RegistrationFormData {
  nome: string
  email: string
  senha: string
  confirmarSenha: string
  cpf: string
  telefone: string
  dataNascimento: string
  tipo: 'membro' | 'empresa'
  // Campos específicos para empresa
  cnpj?: string
  endereco?: string
}

export default function RegistroPage() {
  const router = useRouter()
  const [formData, setFormData] = useState<RegistrationFormData>({
    nome: '',
    email: '',
    senha: '',
    confirmarSenha: '',
    cpf: '',
    telefone: '',
    dataNascimento: '',
    tipo: 'membro'
  })
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [isLoading, setIsLoading] = useState(false)

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
    
    // Limpar erro quando usuário começar a digitar
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {}

    // Validação básica
    if (!formData.nome.trim()) newErrors.nome = 'Nome é obrigatório'
    if (!formData.email.trim()) newErrors.email = 'Email é obrigatório'
    if (!formData.senha) newErrors.senha = 'Senha é obrigatória'
    if (!formData.confirmarSenha) newErrors.confirmarSenha = 'Confirmação de senha é obrigatória'
    
    // Validação de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (formData.email && !emailRegex.test(formData.email)) {
      newErrors.email = 'Email inválido'
    }

    // Validação de senha
    if (formData.senha && formData.senha.length < 6) {
      newErrors.senha = 'Senha deve ter pelo menos 6 caracteres'
    }

    // Validação de confirmação de senha
    if (formData.senha !== formData.confirmarSenha) {
      newErrors.confirmarSenha = 'Senhas não coincidem'
    }

    // Validação específica por tipo
    if (formData.tipo === 'membro') {
      if (!formData.cpf.trim()) newErrors.cpf = 'CPF é obrigatório'
      if (!formData.dataNascimento) newErrors.dataNascimento = 'Data de nascimento é obrigatória'
      
      // Validação básica de CPF (formato)
      const cpfRegex = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/
      if (formData.cpf && !cpfRegex.test(formData.cpf)) {
        newErrors.cpf = 'CPF deve estar no formato 000.000.000-00'
      }
    } else {
      if (!formData.cnpj?.trim()) newErrors.cnpj = 'CNPJ é obrigatório'
      
      // Validação básica de CNPJ (formato)
      const cnpjRegex = /^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/
      if (formData.cnpj && !cnpjRegex.test(formData.cnpj)) {
        newErrors.cnpj = 'CNPJ deve estar no formato 00.000.000/0000-00'
      }
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!validateForm()) return

    setIsLoading(true)
    
    try {
      const endpoint = formData.tipo === 'membro' ? '/api/auth/register/membro' : '/api/auth/register/empresa'
      
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}${endpoint}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      })

      if (response.ok) {
        const data = await response.json()
        // Redirecionar para login com sucesso
        router.push('/auth/login?message=Registro realizado com sucesso!')
      } else {
        const errorData = await response.json()
        setErrors({ submit: errorData.message || 'Erro ao realizar registro' })
      }
    } catch (error) {
      console.error('Erro no registro:', error)
      setErrors({ submit: 'Erro de conexão. Tente novamente.' })
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md mx-auto">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Criar Conta</h1>
          <p className="text-gray-600 mt-2">Entre para nossa plataforma</p>
        </div>

        <div className="bg-white rounded-lg shadow-xl p-8">
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Seletor de tipo */}
            <div>
              <label htmlFor="tipo" className="block text-sm font-medium text-gray-700 mb-2">
                Tipo de cadastro
              </label>
              <select
                id="tipo"
                name="tipo"
                value={formData.tipo}
                onChange={handleInputChange}
                title="Selecione o tipo de cadastro"
                aria-label="Tipo de cadastro"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="membro">Membro</option>
                <option value="empresa">Empresa</option>
              </select>
            </div>

            {/* Nome */}
            <div>
              <label htmlFor="nome" className="block text-sm font-medium text-gray-700 mb-1">
                {formData.tipo === 'empresa' ? 'Nome da Empresa' : 'Nome Completo'}
              </label>
              <input
                id="nome"
                type="text"
                name="nome"
                value={formData.nome}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                  errors.nome ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder={formData.tipo === 'empresa' ? 'Nome da sua empresa' : 'Seu nome completo'}
              />
              {errors.nome && <p className="text-red-500 text-sm mt-1">{errors.nome}</p>}
            </div>

            {/* Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
                Email
              </label>
              <input
                id="email"
                type="email"
                name="email"
                value={formData.email}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                  errors.email ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder="seu@email.com"
              />
              {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
            </div>

            {/* Campos específicos por tipo */}
            {formData.tipo === 'membro' ? (
              <>
                <div>
                  <label htmlFor="cpf" className="block text-sm font-medium text-gray-700 mb-1">
                    CPF
                  </label>
                  <input
                    id="cpf"
                    type="text"
                    name="cpf"
                    value={formData.cpf}
                    onChange={handleInputChange}
                    className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                      errors.cpf ? 'border-red-500' : 'border-gray-300'
                    }`}
                    placeholder="000.000.000-00"
                  />
                  {errors.cpf && <p className="text-red-500 text-sm mt-1">{errors.cpf}</p>}
                </div>

                <div>
                  <label htmlFor="dataNascimento" className="block text-sm font-medium text-gray-700 mb-1">
                    Data de Nascimento
                  </label>
                  <input
                    id="dataNascimento"
                    type="date"
                    name="dataNascimento"
                    value={formData.dataNascimento}
                    onChange={handleInputChange}
                    className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                      errors.dataNascimento ? 'border-red-500' : 'border-gray-300'
                    }`}
                  />
                  {errors.dataNascimento && <p className="text-red-500 text-sm mt-1">{errors.dataNascimento}</p>}
                </div>

                <div>
                  <label htmlFor="telefone" className="block text-sm font-medium text-gray-700 mb-1">
                    Telefone (opcional)
                  </label>
                  <input
                    id="telefone"
                    type="tel"
                    name="telefone"
                    value={formData.telefone}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="(11) 99999-9999"
                  />
                </div>
              </>
            ) : (
              <>
                <div>
                  <label htmlFor="cnpj" className="block text-sm font-medium text-gray-700 mb-1">
                    CNPJ
                  </label>
                  <input
                    id="cnpj"
                    type="text"
                    name="cnpj"
                    value={formData.cnpj || ''}
                    onChange={handleInputChange}
                    className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                      errors.cnpj ? 'border-red-500' : 'border-gray-300'
                    }`}
                    placeholder="00.000.000/0000-00"
                  />
                  {errors.cnpj && <p className="text-red-500 text-sm mt-1">{errors.cnpj}</p>}
                </div>

                <div>
                  <label htmlFor="endereco" className="block text-sm font-medium text-gray-700 mb-1">
                    Endereço (opcional)
                  </label>
                  <input
                    id="endereco"
                    type="text"
                    name="endereco"
                    value={formData.endereco || ''}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Endereço da empresa"
                  />
                </div>
              </>
            )}

            {/* Senha */}
            <div>
              <label htmlFor="senha" className="block text-sm font-medium text-gray-700 mb-1">
                Senha
              </label>
              <input
                id="senha"
                type="password"
                name="senha"
                value={formData.senha}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                  errors.senha ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder="Mínimo 6 caracteres"
              />
              {errors.senha && <p className="text-red-500 text-sm mt-1">{errors.senha}</p>}
            </div>

            {/* Confirmar Senha */}
            <div>
              <label htmlFor="confirmarSenha" className="block text-sm font-medium text-gray-700 mb-1">
                Confirmar Senha
              </label>
              <input
                id="confirmarSenha"
                type="password"
                name="confirmarSenha"
                value={formData.confirmarSenha}
                onChange={handleInputChange}
                className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                  errors.confirmarSenha ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder="Repita sua senha"
              />
              {errors.confirmarSenha && <p className="text-red-500 text-sm mt-1">{errors.confirmarSenha}</p>}
            </div>

            {/* Erro geral */}
            {errors.submit && (
              <div className="bg-red-50 border border-red-200 rounded-md p-3">
                <p className="text-red-600 text-sm">{errors.submit}</p>
              </div>
            )}

            {/* Botão de submit */}
            <button
              type="submit"
              disabled={isLoading}
              className="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isLoading ? 'Criando conta...' : 'Criar Conta'}
            </button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-sm text-gray-600">
              Já tem uma conta?{' '}
              <a href="/auth/login" className="text-blue-600 hover:text-blue-500">
                Faça login
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
