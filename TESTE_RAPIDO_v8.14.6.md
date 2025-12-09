# 🚀 TESTE RÁPIDO v8.14.6

## Como testar a nova funcionalidade em 5 minutos

---

## 1️⃣ Preparação (30 segundos)

1. Abrir navegador
2. Acessar: `http://seu-servidor/lacres_novo.php`
3. Fazer login (se necessário)

---

## 2️⃣ Teste Básico (2 minutos)

### **Passo 1:** Preencher dados do ofício
- Selecionar 2-3 datas
- Preencher alguns lacres IIPR
- Preencher alguns lacres Correios

### **Passo 2:** Digitar etiquetas (NOVO)
- Nos campos de "Etiqueta Correios"
- Digitar 3-5 etiquetas válidas (35 dígitos)
- Exemplo: `12345678901234567890123456789012345`

### **Passo 3:** Gravar ofício
- Clicar em **"Gravar e Imprimir Correios"**
- Verificar modal aparece com texto:
  ```
  💾 As etiquetas dos Correios serão salvas automaticamente junto com o ofício.
  ```
- Escolher: **"Criar Novo"** ou **"Sobrescrever"**

### **Passo 4:** Verificar resultado
- Alert deve mostrar:
  ```
  Oficio Correios salvo com sucesso! No. 123 - Postos: 5, Lotes: 10
  
  Etiquetas Correios salvas: 5
  ```
- Página redireciona para impressão do ofício

---

## 3️⃣ Validação no Banco (1 minuto)

Abrir MySQL e executar:

```sql
-- Ver último ofício criado
SELECT * FROM controle.ciDespachos 
WHERE grupo = 'CORREIOS' 
ORDER BY id DESC LIMIT 1;

-- Ver etiquetas salvas (NOVO)
SELECT 
    leitura,
    cep,
    sequencial,
    posto,
    data,
    login
FROM servico.ciMalotes 
WHERE tipo = 'Correios' 
ORDER BY data DESC 
LIMIT 10;
```

### **Resultado esperado:**
- Tabela `ciDespachos`: 1 novo registro com grupo='CORREIOS'
- Tabela `ciMalotes`: X novos registros (X = número de etiquetas digitadas)
- Campos `cep` e `sequencial` preenchidos corretamente

---

## 4️⃣ Teste de Compatibilidade (1 minuto)

### **Botão separado ainda funciona?**
1. Clicar em **"Salvar Etiquetas Correios"** (botão separado)
2. Verificar modal aparece
3. Escolher modo e salvar
4. Verificar etiquetas salvam em `ciMalotes`

**Status esperado:** ✅ Funciona normalmente (compatibilidade preservada)

---

## 5️⃣ Teste de Cancelamento (30 segundos)

1. Clicar em **"Gravar e Imprimir Correios"**
2. No modal, clicar em **"Cancelar"**
3. Verificar que nada foi salvo

**Status esperado:** ✅ Operação cancelada, tela permanece inalterada

---

## ✅ CHECKLIST RÁPIDO

Após executar os testes acima, marcar:

- [ ] Modal simplificado aparece (apenas 3 botões)
- [ ] Aviso "etiquetas serão salvas automaticamente" visível
- [ ] Alert de sucesso mostra quantidade de etiquetas salvas
- [ ] Redirect para impressão funciona
- [ ] Dados gravados em `ciMalotes` (verificar no banco)
- [ ] Botão "Salvar Etiquetas Correios" separado ainda funciona
- [ ] Cancelar operação não salva nada

---

## 🐛 Se algo não funcionar:

### **Problema 1:** Modal não aparece
- Abrir console do navegador (F12)
- Verificar erros JavaScript
- Verificar se função `confirmarGravarEImprimir()` existe

### **Problema 2:** Etiquetas não salvam
- Verificar no banco: `SELECT * FROM servico.ciMalotes WHERE tipo='Correios' ORDER BY data DESC LIMIT 10;`
- Se vazio: verificar logs PHP (`error_log`)
- Verificar conexão `$pdo_servico` funciona

### **Problema 3:** Página em branco
- Verificar logs PHP
- Verificar sintaxe: `php -l lacres_novo.php`
- Fazer rollback: `cp lacres_novo.php.v8.14.5.backup lacres_novo.php`

---

## 📊 DIFERENÇAS vs Versão Anterior

| Aspecto | v8.14.5 (anterior) | v8.14.6 (atual) |
|---------|-------------------|-----------------|
| **Etiquetas** | Botão separado apenas | Salvam automaticamente |
| **Modal** | 1 modal (3 botões) | 1 modal (3 botões) + aviso |
| **Passos** | 2 cliques (gravar + salvar etiquetas) | 1 clique (gravar) |
| **Alert** | "Oficio salvo..." | "Oficio salvo... Etiquetas salvas: X" |
| **ciMalotes** | Salvamento manual | Salvamento automático |

---

## 🎉 SUCESSO!

Se todos os testes passaram:
- ✅ v8.14.6 está **FUNCIONANDO CORRETAMENTE**
- ✅ Pronto para uso em **PRODUÇÃO**
- ✅ Documentação completa disponível em:
  - `RELEASE_NOTES_v8.14.6_FINAL.md`
  - `VERSAO_8.14.6_FINAL.md`
  - `CHECKLIST_VALIDACAO_v8.14.6.md`

---

**Tempo total:** ~5 minutos  
**Complexidade:** Baixa  
**Requer acesso ao banco:** Sim (para validação SQL)
