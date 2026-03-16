/**
 * CARRINHO DE COMPRAS
 * Gerenciamento do carrinho usando localStorage
 */

// Obter carrinho do localStorage
function obterCarrinho() {
    try {
        const carrinho = localStorage.getItem('carrinho');
        return carrinho ? JSON.parse(carrinho) : [];
    } catch (e) {
        console.error('Erro ao obter carrinho:', e);
        return [];
    }
}

// Salvar carrinho no localStorage
function salvarCarrinho(carrinho) {
    try {
        localStorage.setItem('carrinho', JSON.stringify(carrinho));
        atualizarContadorCarrinho();
    } catch (e) {
        console.error('Erro ao salvar carrinho:', e);
    }
}

// Adicionar produto ao carrinho
function adicionarAoCarrinho(produtoId) {
    // Buscar informações do produto via AJAX
    fetch(`api/produto.php?id=${produtoId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição');
            }
            return response.json();
        })
        .then(produto => {
            if (!produto.success) {
                mostrarNotificacao('❌ Erro ao adicionar produto!', 'error');
                return;
            }
            
            const carrinho = obterCarrinho();
            
            // Verificar se produto já está no carrinho
            const itemExistente = carrinho.find(item => item.id === produtoId);
            
            if (itemExistente) {
                itemExistente.quantidade++;
            } else {
                carrinho.push({
                    id: produto.data.id,
                    nome: produto.data.nome,
                    preco: parseFloat(produto.data.preco),
                    unidade: produto.data.unidade,
                    imagem: produto.data.imagem_url || 'public/assets/img/produtos/default.jpg',
                    quantidade: 1
                });
            }
            
            salvarCarrinho(carrinho);
            
            // Feedback visual
            mostrarNotificacao('✅ Produto adicionado ao carrinho!', 'success');
        })
        .catch(error => {
            console.error('Erro ao adicionar produto:', error);
            mostrarNotificacao('❌ Erro ao adicionar produto!', 'error');
        });
}

// Remover produto do carrinho
function removerDoCarrinho(produtoId) {
    let carrinho = obterCarrinho();
    carrinho = carrinho.filter(item => item.id !== produtoId);
    salvarCarrinho(carrinho);
    
    // Se estiver na página do carrinho, recarregar
    if (window.location.pathname.includes('carrinho.php')) {
        location.reload();
    }
}

// Atualizar quantidade de um produto
function atualizarQuantidade(produtoId, quantidade) {
    const carrinho = obterCarrinho();
    const item = carrinho.find(item => item.id === produtoId);
    
    if (item) {
        if (quantidade <= 0) {
            removerDoCarrinho(produtoId);
        } else {
            item.quantidade = parseInt(quantidade);
            salvarCarrinho(carrinho);
        }
    }
}

// Limpar carrinho
function limparCarrinho() {
    if (confirm('Deseja realmente limpar o carrinho?')) {
        localStorage.removeItem('carrinho');
        atualizarContadorCarrinho();
        
        if (window.location.pathname.includes('carrinho.php')) {
            location.reload();
        }
    }
}

// Atualizar contador do carrinho
function atualizarContadorCarrinho() {
    try {
        const carrinho = obterCarrinho();
        const totalItens = carrinho.reduce((total, item) => total + item.quantidade, 0);
        
        const contador = document.getElementById('carrinho-count');
        if (contador) {
            contador.textContent = totalItens;
            if (totalItens > 0) {
                contador.style.display = 'flex';
            } else {
                contador.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar contador:', error);
    }
}

// Calcular total do carrinho
function calcularTotalCarrinho() {
    const carrinho = obterCarrinho();
    return carrinho.reduce((total, item) => total + (item.preco * item.quantidade), 0);
}

// Mostrar notificação
function mostrarNotificacao(mensagem, tipo = 'success') {
    // Criar elemento de notificação
    const notificacao = document.createElement('div');
    notificacao.className = `notificacao notificacao-${tipo}`;
    notificacao.textContent = mensagem;
    notificacao.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${tipo === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        padding: 1rem 2rem;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notificacao);
    
    // Remover após 3 segundos
    setTimeout(() => {
        notificacao.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notificacao.remove(), 300);
    }, 3000);
}

// Adicionar animações CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
