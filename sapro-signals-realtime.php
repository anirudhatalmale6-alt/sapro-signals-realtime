<?php
/**
 * Plugin Name: Sapro Signals Realtime
 * Description: Display Supabase rt_signals table with real-time updates
 * Version: 1.0.0
 * Author: Freelancer Dev
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================
// CONFIGURATION - Edit these values for your Supabase project
// ============================================================
define('SAPRO_SUPABASE_URL', 'https://dklmoiuhyfpxxsvcdxwk.supabase.co');
define('SAPRO_SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRrbG1vaXVoeWZweHhzdmNkeHdrIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjE4MzI4ODAsImV4cCI6MjA3NzQwODg4MH0.tj1mPIjUr3ajbswtv3eBNN5CSKyeSw_XbsBZ_tcABdE');
define('SAPRO_TABLE_NAME', 'rt_signals');

/**
 * Enqueue Supabase JS client
 */
function sapro_enqueue_scripts() {
    // Supabase JS client v2
    wp_enqueue_script(
        'supabase-js',
        'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js',
        array(),
        '2.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'sapro_enqueue_scripts');

/**
 * Shortcode: [sapro_signals]
 * Renders the real-time signals table
 */
function sapro_signals_shortcode($atts) {
    // Parse shortcode attributes (allows override)
    $atts = shortcode_atts(array(
        'url'   => SAPRO_SUPABASE_URL,
        'key'   => SAPRO_SUPABASE_ANON_KEY,
        'table' => SAPRO_TABLE_NAME,
    ), $atts, 'sapro_signals');

    ob_start();
    ?>
    <!-- Sapro Signals Container -->
    <div id="sapro-signals-wrapper">
        <style>
            /* Dark Theme Styling */
            #sapro-signals-wrapper {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                border-radius: 12px;
                padding: 24px;
                margin: 20px 0;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            }

            #sapro-signals-wrapper .sapro-header {
                text-align: center;
                margin-bottom: 24px;
            }

            #sapro-signals-wrapper .sapro-header h2 {
                color: #00d4ff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 2px;
                text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
            }

            #sapro-signals-wrapper .sapro-table-container {
                overflow-x: auto;
                border-radius: 8px;
                background: rgba(0, 0, 0, 0.3);
            }

            #sapro-signals-wrapper table {
                width: 100%;
                border-collapse: collapse;
                min-width: 600px;
            }

            #sapro-signals-wrapper thead {
                background: linear-gradient(90deg, #0f3460 0%, #1a1a4e 100%);
            }

            #sapro-signals-wrapper th {
                padding: 16px 12px;
                text-align: left;
                color: #00d4ff;
                font-weight: 600;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 1px;
                border-bottom: 2px solid #00d4ff;
            }

            #sapro-signals-wrapper td {
                padding: 14px 12px;
                color: #e0e0e0;
                font-size: 14px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            #sapro-signals-wrapper tbody tr {
                transition: all 0.3s ease;
            }

            #sapro-signals-wrapper tbody tr:hover {
                background: rgba(0, 212, 255, 0.1);
            }

            #sapro-signals-wrapper tbody tr:nth-child(even) {
                background: rgba(255, 255, 255, 0.02);
            }

            #sapro-signals-wrapper tbody tr:nth-child(even):hover {
                background: rgba(0, 212, 255, 0.1);
            }

            /* Row animation for new/updated entries */
            #sapro-signals-wrapper tbody tr.sapro-new-row {
                animation: sapro-highlight 2s ease-out;
            }

            @keyframes sapro-highlight {
                0% { background: rgba(0, 212, 255, 0.4); }
                100% { background: transparent; }
            }

            #sapro-signals-wrapper .sapro-footer {
                text-align: center;
                margin-top: 20px;
                padding-top: 16px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }

            #sapro-signals-wrapper .sapro-footer p {
                color: #888;
                font-size: 14px;
                margin: 0;
            }

            #sapro-signals-wrapper .sapro-loading {
                text-align: center;
                padding: 40px;
                color: #00d4ff;
            }

            #sapro-signals-wrapper .sapro-empty {
                text-align: center;
                padding: 40px;
                color: #888;
            }

            /* Status indicator */
            #sapro-signals-wrapper .sapro-status {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                margin-right: 8px;
                background: #00ff88;
                box-shadow: 0 0 10px #00ff88;
                animation: sapro-pulse 2s infinite;
            }

            @keyframes sapro-pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            #sapro-signals-wrapper .sapro-status.disconnected {
                background: #ff4444;
                box-shadow: 0 0 10px #ff4444;
            }
        </style>

        <!-- Header -->
        <div class="sapro-header">
            <h2><span class="sapro-status" id="sapro-connection-status"></span>LATEST SAPRO SWING SIGNALS</h2>
        </div>

        <!-- Table Container -->
        <div class="sapro-table-container">
            <table id="sapro-signals-table">
                <thead id="sapro-table-head">
                    <!-- Headers will be dynamically generated -->
                </thead>
                <tbody id="sapro-table-body">
                    <tr><td colspan="100" class="sapro-loading">Loading signals...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="sapro-footer">
            <p>New Swing Signals Update From 9:30 TO 4 PM EST</p>
        </div>
    </div>

    <script>
    (function() {
        // Configuration from PHP
        const SUPABASE_URL = <?php echo json_encode($atts['url']); ?>;
        const SUPABASE_ANON_KEY = <?php echo json_encode($atts['key']); ?>;
        const TABLE_NAME = <?php echo json_encode($atts['table']); ?>;

        // DOM elements
        const tableHead = document.getElementById('sapro-table-head');
        const tableBody = document.getElementById('sapro-table-body');
        const statusIndicator = document.getElementById('sapro-connection-status');

        // Store current data (keyed by id)
        let signalsData = {};

        // Specific columns to display in order (as requested by client)
        const displayColumns = [
            { key: 'valid_dt', label: 'Valid DT' },
            { key: 'symbol', label: 'Symbol' },
            { key: 'signal_type', label: 'Signal Type' },
            { key: 'entry_date', label: 'Entry Date' },
            { key: 'entry_time', label: 'Entry Time' },
            { key: 'entry_price', label: 'Entry Price' },
            { key: 'stoploss_price', label: 'Stoploss Price' },
            { key: 'pattern_id', label: 'Pattern ID' }
        ];

        // Wait for Supabase to load
        function waitForSupabase(callback) {
            if (typeof supabase !== 'undefined') {
                callback();
            } else {
                setTimeout(() => waitForSupabase(callback), 100);
            }
        }

        waitForSupabase(function() {
            // Initialize Supabase client
            const client = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

            // Fetch initial data
            async function fetchInitialData() {
                try {
                    const { data, error } = await client
                        .from(TABLE_NAME)
                        .select('*')
                        .order('entry_date', { ascending: false })
                        .order('entry_time', { ascending: false });

                    if (error) throw error;

                    if (data && data.length > 0) {
                        // Store data
                        data.forEach(row => {
                            signalsData[row.id] = row;
                        });

                        renderTable();
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="100" class="sapro-empty">No signals available</td></tr>';
                    }
                } catch (err) {
                    console.error('Error fetching data:', err);
                    tableBody.innerHTML = '<tr><td colspan="100" class="sapro-empty">Error loading signals</td></tr>';
                }
            }

            // Render table headers
            function renderHeaders() {
                let headerHtml = '<tr>';
                displayColumns.forEach(col => {
                    headerHtml += `<th>${col.label}</th>`;
                });
                headerHtml += '</tr>';
                tableHead.innerHTML = headerHtml;
            }

            // Render table rows sorted by Entry Date and Entry Time (latest first)
            function renderTable() {
                renderHeaders();

                const sortedData = Object.values(signalsData).sort((a, b) => {
                    // Sort by entry_date descending, then entry_time descending
                    const dateA = a.entry_date || '';
                    const dateB = b.entry_date || '';
                    const timeA = a.entry_time || '';
                    const timeB = b.entry_time || '';

                    if (dateB !== dateA) {
                        return dateB.localeCompare(dateA);
                    }
                    return timeB.localeCompare(timeA);
                });

                if (sortedData.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="100" class="sapro-empty">No signals available</td></tr>';
                    return;
                }

                let bodyHtml = '';
                sortedData.forEach(row => {
                    bodyHtml += `<tr data-id="${row.id}">`;
                    displayColumns.forEach(col => {
                        const value = row[col.key] !== null && row[col.key] !== undefined ? row[col.key] : '-';
                        bodyHtml += `<td>${escapeHtml(String(value))}</td>`;
                    });
                    bodyHtml += '</tr>';
                });
                tableBody.innerHTML = bodyHtml;
            }

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Highlight a row (for new/updated entries)
            function highlightRow(id) {
                const row = tableBody.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.classList.remove('sapro-new-row');
                    void row.offsetWidth; // Trigger reflow
                    row.classList.add('sapro-new-row');
                }
            }

            // Subscribe to real-time changes
            function subscribeToChanges() {
                const channel = client
                    .channel('rt_signals_changes')
                    .on(
                        'postgres_changes',
                        {
                            event: '*',
                            schema: 'public',
                            table: TABLE_NAME
                        },
                        (payload) => {
                            console.log('Realtime event:', payload);

                            switch (payload.eventType) {
                                case 'INSERT':
                                    signalsData[payload.new.id] = payload.new;
                                    renderTable();
                                    highlightRow(payload.new.id);
                                    break;

                                case 'UPDATE':
                                    signalsData[payload.new.id] = payload.new;
                                    renderTable();
                                    highlightRow(payload.new.id);
                                    break;

                                case 'DELETE':
                                    delete signalsData[payload.old.id];
                                    renderTable();
                                    break;
                            }
                        }
                    )
                    .subscribe((status) => {
                        console.log('Subscription status:', status);
                        if (status === 'SUBSCRIBED') {
                            statusIndicator.classList.remove('disconnected');
                        } else if (status === 'CLOSED' || status === 'CHANNEL_ERROR') {
                            statusIndicator.classList.add('disconnected');
                        }
                    });
            }

            // Initialize
            fetchInitialData().then(() => {
                subscribeToChanges();
            });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('sapro_signals', 'sapro_signals_shortcode');
