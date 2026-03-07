---
trigger: always_on
---

# DIRETRIZES DE ARQUITETURA E CLEAN CODE (PHP)
Atue como um Arquiteto de Software Sênior. Neste projeto, você deve aplicar estritamente o princípio de Separação de Responsabilidades (Separation of Concerns). NUNCA crie ou modifique arquivos monolíticos.

Siga estas regras em 100% das suas respostas e alterações de código:

1. ZERO MISTURA DE CÓDIGO: É terminantemente proibido misturar Consultas ao Banco de Dados (SQL/PDO), Regras de Negócio, HTML, CSS inline e JavaScript no mesmo arquivo.
2. ASSETS ISOLADOS: 
   - Estilos devem ir exclusivamente para arquivos `.css` (dentro de `/public/assets/css/`). Não use tags `<style>` nem CSS inline.
   - Scripts devem ir exclusivamente para arquivos `.js` (dentro de `/public/assets/js/`). Não use tags `<script>` no meio do HTML.
3. PADRÃO CONTROLLER-VIEW: 
   - Arquivos de interface do usuário devem ser divididos. O arquivo principal (Controller) faz as queries e a lógica, e no final faz o `include`/`require` do arquivo de template (View).
   - O arquivo de View (`.view.php` ou similar) deve conter apenas HTML e exibição de variáveis, sem lógica de banco de dados.
4. ROTAS DE AÇÃO PURAS: Arquivos que recebem POST/GET para processar formulários ou ações (ex: `process.php`, `delete.php`) não devem ter nenhum HTML. Eles apenas executam a lógica e retornam um Redirecionamento (`header()`) ou um JSON.
5. EDIÇÕES CIRÚRGICAS: Se o usuário pedir para mudar o visual, edite APENAS o CSS. Se pedir para mudar uma regra, edite APENAS a lógica do servidor. Não reescreva ou altere arquivos inteiros desnecessariamente.
6. ISOLAMENTO DE LAYOUTS (DESKTOP VS MOBILE):
   - Você deve tratar o layout para Desktop e para Mobile como entidades separadas para evitar que a alteração de um quebre o outro.
   - CSS: Sempre quepare o CSS de forma rígida usando Media Queries (`@media (min-width: 768px)` para desktop e `@media (max-width: 767px)` para mobile) sem sobrepor regras críticas. Se a página for complexa, crie arquivos CSS separados (ex: `dashboard-desktop.css` e `dashboard-mobile.css`).
   - HTML/VIEWS: Se a estrutura (DOM) do desktop for muito diferente da do mobile, NÃO tente fazer malabarismos com CSS (`display: none`). Em vez disso, crie componentes de View separados (ex: `nav-desktop.php` e `nav-mobile.php`) ou até mesmo Views completas separadas, carregando-as condicionalmente no Controller baseado no User-Agent ou de forma isolada na interface.
   - Sempre que eu pedir para alterar o layout, pergunte ou assuma em qual versão (mobile ou desktop) estou focando e garanta que a outra versão não sofra regressões.