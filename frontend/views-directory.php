<?php
/**
 * Public facing storefront marketplace catalog view template.
 * Hardened Path Alignment Setup featuring dynamic pagination and filtering metrics.
 *
 * @package    Wpf_Engine
 * @subpackage Wpf_Engine/frontend
 */

// Block direct lifecycle access requests for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

/**
 * Hardened Path URL Correction Engine
 * This file lives inside: /wp-content/plugins/wpfetcher-engine/frontend/views-directory.php
 * dirname(__FILE__) evaluates to the "frontend" folder wrapper.
 * plugins_url() targeting the parent path maps the browser link straight to the root directory
 * where our structural 'library.json' flat file is kept away from theme template markup overrides.
 */
$plugin_parent_folder = dirname( dirname( __FILE__ ) ); // Drops us back out to plugin parent directory name
$current_plugin_slug  = plugin_basename( $plugin_parent_folder );

$json_disk_file_path  = WP_PLUGIN_DIR . '/' . $current_plugin_slug . '/library.json';
$json_browser_web_url = plugins_url( 'library.json', $plugin_parent_folder . '/library.json' );

// Execute local file diagnostics check before launching app hooks
$server_disk_file_vetted = file_exists( $json_disk_file_path ) ? 'true' : 'false';
?>

<div id="wpf-marketplace-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    
    <header style="margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #0f172a; margin: 0 0 8px 0;">Developer Boilerplate Directory</h1>
            <p style="color: #64748b; margin: 0; font-size: 15px;">Instantly search, categorize, and extract clean-code WordPress and ClassicPress architecture wrappers.</p>
        </div>
        <div>
            <input type="text" id="wpf-search-box" placeholder="🔍 Search approved assets..." style="width: 320px; padding: 10px 16px; border: 2px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; transition: border-color 0.15s ease;" oninput="wpfApp.handleSearchAndFilter()">
        </div>
    </header>

    <nav id="wpf-filter-tabs" style="display: flex; gap: 12px; margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px;">
        <button class="wpf-tab" id="wpf-tab-default-active" onclick="wpfApp.switchCategory('all', this)" style="padding: 8px 16px; background: #0f172a; color: #ffffff; border: none; font-weight: 600; font-size: 13px; border-radius: 20px; cursor: pointer; transition: all 0.15s ease;">All Components</button>
        <button class="wpf-tab" onclick="wpfApp.switchCategory('options-wrappers', this)" style="padding: 8px 16px; background: #f1f5f9; color: #475569; border: none; font-weight: 600; font-size: 13px; border-radius: 20px; cursor: pointer; transition: all 0.15s ease;">⚙️ Options Panels</button>
        <button class="wpf-tab" onclick="wpfApp.switchCategory('cpt-registrars', this)" style="padding: 8px 16px; background: #f1f5f9; color: #475569; border: none; font-weight: 600; font-size: 13px; border-radius: 20px; cursor: pointer; transition: all 0.15s ease;">📁 CPT Registrars</button>
        <button class="wpf-tab" onclick="wpfApp.switchCategory('http-clients', this)" style="padding: 8px 16px; background: #f1f5f9; color: #475569; border: none; font-weight: 600; font-size: 13px; border-radius: 20px; cursor: pointer; transition: all 0.15s ease;">⚡ HTTP Clients</button>
    </nav>

    <main id="wpf-directory-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px; min-height: 250px;">
        <div style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 40px;">⏳ Streaming assets from secure local data payload registry...</div>
    </main>

    <div id="wpf-pagination-controls" style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #f1f5f9;"></div>

    <div id="wpf-config-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); justify-content: center; align-items: center; z-index: 99999;">
        <div style="background: #ffffff; padding: 32px; border-radius: 12px; max-width: 460px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); position: relative;">
            <button onclick="wpfApp.closeModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; font-weight: bold;">&times;</button>
            
            <h3 id="modal-title" style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #0f172a;">Configure Boilerplate Asset</h3>
            <p style="color: #64748b; font-size: 13px; margin: 0 0 24px 0;">Your custom inputs will safely populate and replace the template placeholder tokens in real time.</p>
            
            <form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST">
                <input type="hidden" name="action" value="wpf_generate_boilerplate">
                <input type="hidden" name="wpf_token" value="<?php echo wp_create_nonce( 'wpf_frontend_download' ); ?>">
                <input type="hidden" name="template_file" id="modal-template-file" value="">

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Extension / Plugin Name</label>
                    <input type="text" name="plugin_name" placeholder="e.g., Acme Options Kit" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">PHP Class Prefix Namespace</label>
                    <input type="text" name="plugin_prefix" placeholder="e.g., Acme_Options_Kit" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">Text Domain Slug Identifier</label>
                    <input type="text" name="text_domain" placeholder="e.g., acme-options-kit" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="button" onclick="wpfApp.closeModal()" style="flex: 1; padding: 12px; background: #f1f5f9; border: none; color: #475569; font-weight: 600; border-radius: 6px; cursor: pointer;">Cancel</button>
                    <button type="submit" style="flex: 2; padding: 12px; background: #2563eb; border: none; color: #ffffff; font-weight: 600; border-radius: 6px; cursor: pointer; transition: background 0.15s ease;">Compile &amp; Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
/**
 * WPFetcher Reactive Frontend Application Controller Architecture
 */
const wpfApp = {
    // Structural Initialization Parameters
    rawLibrary: [],
    filteredDataset: [],
    activeCategory: 'all',
    
    // Pagination Constraints Settings
    currentPage: 1,
    itemsPerPage: 6,

    init: async function() {
        const fileExistsOnServer = <?php echo $server_disk_file_vetted; ?>;
        const libraryUrlTarget = '<?php echo esc_url( $json_browser_web_url ); ?>';
        
        console.log('WPFetcher Bootstrap Sequence Engaged.');
        console.log('Target JSON Payload Path Resolve Location ->', libraryUrlTarget);
        console.log('Server Disk State Verification Flag ->', fileExistsOnServer);

        const grid = document.getElementById('wpf-directory-grid');

        // Prevent network request failure if file hasn't been generated yet
        if (!fileExistsOnServer) {
            console.error('WPFetcher Error: library.json was not detected inside the plugin root folder directory.');
            grid.innerHTML = `
                <div style="grid-column: 1/-1; padding: 40px; text-align: center; color: #ef4444; font-weight: 600; border: 1px dashed #fee2e2; background: #fef2f2; border-radius: 8px;">
                    ❌ Local Data Payload File (library.json) Missing.<br>
                    <span style="font-size:12px; font-weight:400; color:#7f1d1d; margin-top:8px; display:block;">
                        Navigate to your admin panel vetting workspace queue, approve your gathered items, and let the backend automatically compile your index array.
                    </span>
                </div>`;
            return;
        }

        try {
            // Fetch payload index avoiding browser aggressive routing caches
            const response = await fetch(libraryUrlTarget + '?cache_bust=' + Date.now());
            
            // Log raw text snapshot for precision diagnostics if network path is crossed
            const rawResponseText = await response.clone().text();
            console.log('Raw payload character trace string snapshot (First 50 chars):', rawResponseText.substring(0, 50));
            
            if (!response.ok) throw new Error('Data stream read connection network failure. HTTP Code ' + response.status);
            
            this.rawLibrary = await response.json();
            console.log('WPFetcher Success: Directory Payload parsed completely. Asset Count:', this.rawLibrary.length);
            
            this.handleSearchAndFilter();
        } catch (error) {
            console.error('WPFetcher Application Crash Stack Trace:', error);
            grid.innerHTML = `
                <div style="grid-column: 1/-1; padding: 40px; text-align: center; color: #ef4444; font-weight: 600; border: 1px dashed #fee2e2; background: #fef2f2; border-radius: 8px;">
                    ❌ Application Data Execution Aborted.<br>
                    <span style="font-size:13px; font-family:monospace; color:#b91c1c; font-weight:700; display:block; margin-top:8px;">Diagnostic context: ${error.message}</span>
                    <span style="font-size:11px; font-family:monospace; color:#451a03; display:block; margin-top:4px; font-weight:400; text-align:left; background:#fff7ed; padding:10px; border:1px solid #ffedd5;">
                        Check your DevTools Console (F12) to inspect the <strong>"Raw payload character trace string snapshot"</strong> log readout. It will show exactly what text or PHP warning fouled the operation.
                    </span>
                </div>`;
        }
    },

    switchCategory: function(targetCat, buttonEl) {
        this.activeCategory = targetCat;
        this.currentPage = 1; 
        
        document.querySelectorAll('.wpf-tab').forEach(tab => {
            tab.style.background = '#f1f5f9';
            tab.style.color = '#475569';
        });

        if (buttonEl) {
            buttonEl.style.background = '#0f172a';
            buttonEl.style.color = '#ffffff';
        }

        this.handleSearchAndFilter();
    },

    handleSearchAndFilter: function() {
        const searchBox = document.getElementById('wpf-search-box');
        const searchQuery = searchBox ? searchBox.value.toLowerCase().trim() : '';

        this.filteredDataset = this.rawLibrary.filter(item => {
            const matchesCategory = (this.activeCategory === 'all' || item.category === this.activeCategory);
            const matchesSearch    = !searchQuery || 
                                     item.name.toLowerCase().includes(searchQuery) || 
                                     (item.description && item.description.toLowerCase().includes(searchQuery)) ||
                                     item.author.toLowerCase().includes(searchQuery);

            return matchesCategory && matchesSearch;
        });

        this.renderCardsGrid();
    },

    renderCardsGrid: function() {
        const grid = document.getElementById('wpf-directory-grid');
        
        if (this.filteredDataset.length === 0) {
            grid.innerHTML = `
                <div style="grid-column: 1/-1; padding: 50px; text-align: center; color: #64748b; background: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 12px;">
                    <span style="font-size: 24px; display: block; margin-bottom: 10px;">📋</span>
                    No premium blueprints match your filtered search combinations.
                </div>`;
            document.getElementById('wpf-pagination-controls').innerHTML = '';
            return;
        }

        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageSlice = this.filteredDataset.slice(startIndex, endIndex);

        grid.innerHTML = pageSlice.map(item => {
            let badgeLabel = '⚙️ Options Panel';
            let badgeColor = '#10b981';

            if (item.category === 'cpt-registrars') {
                badgeLabel = '📁 CPT Registrar';
                badgeColor = '#0ea5e9';
            } if (item.category === 'http-clients') {
                badgeLabel = '⚡ HTTP Client';
                badgeColor = '#a855f7';
            } else if (item.category === 'dashboard-widgets') {
                badgeLabel = 'Dashboard Widgets';
                badgeColor = '#009f5f';
            }

            return `
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: transform 0.2s ease, box-shadow 0.2s ease;" 
                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.05)'" 
                     onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)'">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 10px;">
                            <span style="background: ${badgeColor}15; color: ${badgeColor}; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">${badgeLabel}</span>
                            <span style="font-size: 12px; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 4px; padding-top: 2px;">⭐ ${item.stars || 0}</span>
                        </div>
                        <h3 style="font-size: 18px; color: #1e293b; margin: 0 0 8px 0; font-weight: 700; line-height:1.4;">${item.name}</h3>
                        <p style="font-size: 13px; color: #64748b; line-height: 1.6; margin: 0 0 20px 0; min-height: 55px;">${item.description || 'Pristine, clean-architecture component helper template blueprint container.'}</p>
                        <div style="font-size: 12px; color: #94a3b8; margin-bottom: 12px;">
                            By @${item.author} &bull; Updated ${item.last_updated ? item.last_updated.split(' ')[0] : 'Recently'}
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center; margin-top: auto;">
                        <button onclick="wpfApp.openModal('${item.name.replace(/'/g, "\\'")}', '${item.template_file}')" style="flex: 2; padding: 10px; background: #2563eb; color: #fff; border: none; font-weight: 600; font-size: 13px; border-radius: 6px; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Customize &amp; Fetch</button>
                        <a href="${item.url || '#'}" target="_blank" rel="noopener noreferrer" style="padding: 10px; background: #f1f5f9; border: none; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: background 0.15s ease;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'" title="View Source Blueprint Repository">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>
                        </a>
                    </div>
                </div>
            `;
        }).join('');

        this.renderPaginationControls();
    },

    renderPaginationControls: function() {
        const controlsContainer = document.getElementById('wpf-pagination-controls');
        const totalPages = Math.ceil(this.filteredDataset.length / this.itemsPerPage);

        if (totalPages <= 1) {
            controlsContainer.innerHTML = '';
            return;
        }

        let controlsHtml = '';

        controlsHtml += `
            <button onclick="wpfApp.changePage(${this.currentPage - 1})" ${this.currentPage === 1 ? 'disabled' : ''} style="padding: 6px 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; color: #475569; font-weight: 600; font-size: 13px; cursor: ${this.currentPage === 1 ? 'not-allowed' : 'pointer'}; opacity: ${this.currentPage === 1 ? '0.4' : '1'}; transition: all 0.1s ease;">&larr; Prev</button>
        `;

        for (let i = 1; i <= totalPages; i++) {
            const isActive = i === this.currentPage;
            controlsHtml += `
                <button onclick="wpfApp.changePage(${i})" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: ${isActive ? '#0f172a' : '#ffffff'}; border: 1px solid ${isActive ? '#0f172a' : '#e2e8f0'}; border-radius: 6px; color: ${isActive ? '#ffffff' : '#475569'}; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.1s ease;">${i}</button>
            `;
        }

        controlsHtml += `
            <button onclick="wpfApp.changePage(${this.currentPage + 1})" ${this.currentPage === totalPages ? 'disabled' : ''} style="padding: 6px 12px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; color: #475569; font-weight: 600; font-size: 13px; cursor: ${this.currentPage === totalPages ? 'not-allowed' : 'pointer'}; opacity: ${this.currentPage === totalPages ? '0.4' : '1'}; transition: all 0.1s ease;">Next &rarr;</button>
        `;

        controlsContainer.innerHTML = controlsHtml;
    },

    changePage: function(newPage) {
        const totalPages = Math.ceil(this.filteredDataset.length / this.itemsPerPage);
        if (newPage < 1 || newPage > totalPages) return;

        this.currentPage = newPage;
        this.renderCardsGrid();

        const tabsNav = document.getElementById('wpf-filter-tabs');
        if (tabsNav) tabsNav.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    },

    openModal: function(assetName, templateFile) {
        document.getElementById('modal-title').innerText = `Configure ${assetName}`;
        document.getElementById('modal-template-file').value = templateFile;
        const modal = document.getElementById('wpf-config-modal');
        modal.style.display = 'flex';
        
        setTimeout(() => modal.querySelector('input[name="plugin_name"]').focus(), 50);
    },

    closeModal: function() {
        document.getElementById('wpf-config-modal').style.display = 'none';
    }
};

// Bind activation bootstrap loop right on DOM load completions
document.addEventListener('DOMContentLoaded', () => wpfApp.init());
</script>

<?php 
get_footer(); 
?>