<?php

// log the script running time
include_once (__DIR__ . '/AuditScriptsFunctions.php');
global $Title;
RecordRunningTime($Title, $_SESSION['UserID']);

echo '<div id="mask">
		<div id="dialog"></div>
	</div>';

if (isset($Messages) and count($Messages) > 0) {
	$LogFile = false;

	if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 0) { // add these 3 lines
		if ($_SESSION['LogPath'] == '') {
			$_SESSION['LogPath'] = 'companies/' . $_SESSION['DatabaseName'] . '/logs';
			if (!file_exists($_SESSION['LogPath'])) {
				mkdir($_SESSION['LogPath']);
			}
		}
		$LogFile = fopen($_SESSION['LogPath'] . '/weberp-' . $_SESSION['DatabaseName'] . '-' . date('Y-m-d') . '.log', 'a');
	}

	echo '<div id="MessageContainerFoot">';

	foreach ($Messages as $Message) {
		switch ($Message[1]) {
			case 'error':
				$Class = 'error';
				$Message[2] = $Message[2] ? $Message[2] : __('ERROR') . ' ' . __('Report');
				if (!empty($LogFile) && isset($_SESSION['LogSeverity']) && $_SESSION['LogSeverity'] > 0) {
					fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Message[2] . ',' . $_SESSION['UserID'] . ',' . strip_tags(trim($Message[0], ',')) . "\n");
				}
			    break;

			case 'warn':
			case 'warning':
				$Class = 'warn';
				$Message[2] = $Message[2] ? $Message[2] : __('WARNING') . ' ' . __('Report');
				if (!empty($LogFile) && isset($_SESSION['LogSeverity']) && $_SESSION['LogSeverity'] > 1) {
					fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Message[2] . ',' . $_SESSION['UserID'] . ',' . strip_tags(trim($Message[0], ',')) . "\n");
				}
				break;

            case 'info':
                $Class = 'info';
                $Message[2] = $Message[2] ? $Message[2] : __('INFORMATION') . ' ' . __('Message');
				if (!empty($LogFile) && isset($_SESSION['LogSeverity']) && $_SESSION['LogSeverity'] > 2) {
					fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Message[2] . ',' . $_SESSION['UserID'] . ',' . strip_tags(trim($Message[0], ',')) . "\n");
				}
				break;

            case 'success':
			default:
                $Class = 'success';
                $Message[2] = $Message[2] ? $Message[2] : __('SUCCESS') . ' ' . __('Report');
				if (!empty($LogFile) && isset($_SESSION['LogSeverity']) && $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Message[2] . ',' . $_SESSION['UserID'] . ',' . strip_tags(trim($Message[0], ',')) . "\n");
				}
		}

		                // Prettify the message title
                $title = $Message[2];
                $title = str_ireplace(array(' Report', ' Message'), '', $title);
                
                $icon = '';
                if ($Class == 'success') $icon = '<i class="fas fa-check-circle" style="color: #10B981;"></i>';
                else if ($Class == 'error') $icon = '<i class="fas fa-exclamation-circle" style="color: #EF4444;"></i>';
                else if ($Class == 'warn') $icon = '<i class="fas fa-exclamation-triangle" style="color: #F59E0B;"></i>';
                else $icon = '<i class="fas fa-info-circle" style="color: #3B82F6;"></i>';

                echo '<div class="aw-toast aw-toast-', $Class, ' noPrint">
                        <div class="aw-toast-icon">', $icon, '</div>
                        <div class="aw-toast-content">
                                <div class="aw-toast-title">', $title, '</div>
                                <div class="aw-toast-msg">', $Message[0], '</div>
                        </div>
                        <button type="button" class="aw-toast-close" onclick="this.parentElement.style.display=\'none\';">&times;</button>
                      </div>';
	}

	        if (!empty($LogFile)) {
                fclose($LogFile);
        }
        
        echo '<style>
        #MessageContainerFoot {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }
        .aw-toast {
            pointer-events: auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0,0,0,0.05);
            display: flex;
            align-items: flex-start;
            padding: 16px;
            min-width: 320px;
            max-width: 450px;
            border-left: 5px solid #3B82F6;
            animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .aw-toast-success { border-left-color: #10B981; }
        .aw-toast-error { border-left-color: #EF4444; }
        .aw-toast-warn { border-left-color: #F59E0B; }
        .aw-toast-icon {
            font-size: 1.25rem;
            margin-right: 14px;
            flex-shrink: 0;
            padding-top: 1px;
        }
        .aw-toast-content {
            flex-grow: 1;
            margin-right: 28px;
        }
        .aw-toast-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #111827;
            margin-bottom: 4px;
            text-transform: capitalize;
            line-height: 1.2;
        }
        .aw-toast-msg {
            font-size: 0.875rem;
            color: #4B5563;
            line-height: 1.5;
        }
        .aw-toast-close {
            position: absolute;
            top: 14px;
            right: 14px;
            background: none;
            border: none;
            color: #9CA3AF;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 2px 6px;
            line-height: 0.8;
            transition: all 0.2s;
            border-radius: 4px;
        }
        .aw-toast-close:hover { 
            color: #111827; 
            background: #F3F4F6;
        }
        @keyframes slideInRight {
            from { transform: translateX(120%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        </style>';
        
        echo '<script>
        setTimeout(function() {
            var toasts = document.querySelectorAll(".aw-toast");
            toasts.forEach(function(toast) {
                toast.style.transition = "opacity 0.4s ease-out, transform 0.4s ease-out";
                toast.style.opacity = "0";
                toast.style.transform = "translateX(100%)";
                setTimeout(function() { toast.remove(); }, 400);
            });
        }, 5000);
        </script>';
        
        echo '</div>';
}

echo '</section>'; // MainBody
echo '</div>'; // dashboard-content

if (!isset($NoMenu) || $NoMenu != 1) {
	echo '</div>'; // dashboard-container
	echo '<footer class="noPrint">
			<a class="FooterLogo" href="https://www.weberp.org" target="_blank">
				<div class="logo logo-left">web</div><div class="logo logo-right"><i>ERP</i></div>
			</a>
		  </footer>'; // FooterDiv
} else {
	echo '</div>'; // dashboard-container-standalone
}
echo '</body>';
echo '</html>';
