# Arquitetura de Produtos - Sistema Expedisoft

## 📋 Resumo da Decisão Arquitetural

**Decisão:** Manter a tabela `products` separada com normalização adequada.

**Justificativa:** Para um projeto de TCC, esta abordagem demonstra:
- Conhecimento de normalização de banco de dados
- Arquitetura escalável e profissional
- Redução de redundância de dados
- Facilita relatórios e análises futuras

---

## 🏗️ Estrutura das Tabelas

### 1. **products** (Catálogo de Produtos)
```
- id (UUID)
- sku (UNIQUE) - Código único do produto
- description - Descrição do produto
- weight - Peso padrão
- unit - Unidade de medida (un, kg, cx, etc)
- barcode - Código de barras
- timestamps
```

**Propósito:** Centralizar informações de produtos, evitando duplicação.

### 2. **order_items** (Itens do Pedido)
```
- id (UUID)
- loading_order_id (FK) - Referência à ordem
- product_id (FK) - Referência ao produto
- quantity - Quantidade total do item
- note - Observações específicas
- timestamps
```

**Propósito:** Relacionar produtos com ordens de carregamento.

### 3. **packages** (Volumes/Pacotes)
```
- id (UUID)
- order_item_id (FK) - Referência ao item
- unique_package_code (UNIQUE) - Código QR/Barras do volume
- quantity_in_package - Quantidade dentro deste volume
- timestamps
```

**Propósito:** Rastrear volumes individuais (cada caixa/pacote).

---

## 🔄 Fluxo de Integração

### Payload Recebido
```json
{
  "source_system": "ERP_MATRIZ",
  "loadingOrder": {
    "external_id": "ORD-12345",
    "issue_date": "2026-02-09",
    "items": [
      {
        "product_sku": "PROD-001",
        "product_description": "Cadeira Gamer RGB",
        "quantity": 10,
        "weight": 15.5,
        "unit": "un",
        "barcode": "7891234567890",
        "unique_package_code": "PKG-001-A",
        "quantity_in_package": 10,
        "note": "Manusear com cuidado"
      }
    ]
  }
}
```

### Processamento (LoadingOrderService)

1. **Busca/Cria Entidades:**
   - Customer
   - Destination
   - Carrier
   - Vehicle
   - Driver

2. **Cria Loading Order**

3. **Para cada item:**
   
   a) **Busca/Cria Produto** (EntityService::findOrCreateProduct)
   ```php
   - Busca por SKU
   - Se existe: atualiza informações
   - Se não existe: cria novo
   ```
   
   b) **Cria Order Item**
   ```php
   - Referencia o product_id
   - Define quantidade total
   - Adiciona nota (se houver)
   ```
   
   c) **Cria Package** (se houver código único)
   ```php
   - Associa ao order_item
   - Define código único (QR/Barras)
   - Define quantidade no volume
   ```

---

## ✅ Vantagens desta Arquitetura

### 1. **Normalização Adequada**
- Produtos não ficam duplicados
- Alterações no produto se propagam
- Histórico consistente

### 2. **Rastreabilidade**
- Saber quais produtos foram mais carregados
- Análise de performance por SKU
- Relatórios de produtos mais problemáticos

### 3. **Escalabilidade**
- Fácil adicionar categorias de produtos
- Possível implementar gestão de estoque
- Integração futura com catálogo de produtos

### 4. **Qualidade para TCC**
- Demonstra conhecimento técnico
- Justificativa arquitetural sólida
- Facilita apresentação de benefícios

---

## 📊 Exemplo de Relacionamentos

```
LoadingOrder (ORD-12345)
│
├─ OrderItem #1
│  ├─ Product (SKU: PROD-001) → Cadeira Gamer
│  ├─ Quantity: 10
│  └─ Package (PKG-001-A) → 10 unidades
│
└─ OrderItem #2
   ├─ Product (SKU: PROD-002) → Mesa Escritório
   ├─ Quantity: 5
   └─ Package (PKG-002-A) → 5 unidades
```

---

## 🔍 Queries Úteis

### Produtos mais carregados
```sql
SELECT p.sku, p.description, SUM(oi.quantity) as total_loaded
FROM products p
JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id
ORDER BY total_loaded DESC;
```

### Ordens de um produto específico
```sql
SELECT lo.external_id, lo.issue_date, oi.quantity
FROM loading_orders lo
JOIN order_items oi ON lo.id = oi.loading_order_id
JOIN products p ON oi.product_id = p.id
WHERE p.sku = 'PROD-001';
```

---

## 🚫 Alternativa NÃO Recomendada

**Desnormalizar tudo em order_items:**
- ❌ Duplicação de dados (descrição, peso, etc)
- ❌ Inconsistências se produto mudar
- ❌ Dificuldade em relatórios
- ❌ Menos profissional para TCC

---

## 📝 Conclusão

A arquitetura atual com **tabela `products` separada** é:
- ✅ Tecnicamente correta
- ✅ Escalável
- ✅ Profissional
- ✅ Ideal para TCC

**Recomendação:** Mantenha esta estrutura e use-a como ponto positivo na apresentação do TCC.
