<style>
    .tooltip {
        position: absolute;
        cursor: pointer;
    }
    
    .tooltip-content {
        visibility: hidden;
        width: 350px; /* Increased width */
        background-color: #fff;
        color: #333;
        text-align: left;
        border-radius: 12px;
        padding: 16px;
        position: absolute;
        z-index: 100; /* Increased z-index */
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 
                    0 10px 10px -5px rgba(0, 0, 0, 0.04);
        max-height: 450px; /* Increased max-height */
        overflow-y: auto;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(209, 213, 219, 0.3);
    }
    
    /* Add positioning alternatives for better visibility */
    .tooltip-content.top {
        bottom: 125%;
    }
    
    .tooltip-content.bottom {
        top: 125%;
        bottom: auto;
    }
    
    .tooltip-content.left {
        right: 125%;
        left: auto;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .tooltip-content.right {
        left: 125%;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .tooltip:hover .tooltip-content {
        visibility: visible;
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
    
    /* Add arrow indicators */
    .tooltip-content::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -10px;
        border-width: 10px;
        border-style: solid;
        border-color: #fff transparent transparent transparent;
    }
    
    .tooltip-title {
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 12px;
        margin-bottom: 12px;
        font-weight: 600;
        color: #1f2937;
        font-size: 1.1em;
    }
    
    .tooltip-item {
        padding: 10px;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s ease;
    }
    
    .tooltip-item:hover {
        background-color: #f9fafb;
        padding-left: 16px;
    }
    
    .tooltip-item > div {
        margin: 4px 0;
        line-height: 1.4;
    }
    
    .tooltip-item:last-child {
        border-bottom: none;
    }

    /* Custom scrollbar */
    .tooltip-content::-webkit-scrollbar {
        width: 6px;
    }

    .tooltip-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .tooltip-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    /* Card improvements */
    .card-hover {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .tooltip-content {
            width: 280px;
            left: 0;
            transform: translateX(0) translateY(10px);
        }
        
        .tooltip:hover .tooltip-content {
            transform: translateX(0) translateY(0);
        }

        /* Badge styles */
        .tooltip-item span {
            display: inline-block;
            margin: 2px 0;
            transition: all 0.2s ease;
        }
        
        .tooltip-item span:hover {
            transform: scale(1.05);
        }
        
        /* Additional spacing for better readability */
        .tooltip-item > div {
            margin: 6px 0;
        }

        /* Badge styles */
        .tooltip-item span {
            display: inline-block;
            margin: 2px 0;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        /* Regular medication status */
        .bg-green-100 {
            background-color: #dcfce7;
            border: 1px solid #86efac;
        }
        
        .text-green-800 {
            color: #166534;
        }
        
        /* Irregular medication status */
        .bg-red-100 {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
        }
        
        .text-red-800 {
            color: #991b1b;
        }
        
        /* Hover effects */
        .tooltip-item span:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Status label spacing */
        .tooltip-item > div {
            margin: 8px 0;
            line-height: 1.5;
        }
        
        /* Status label alignment */
        .tooltip-item strong {
            display: inline-block;
            min-width: 140px;
            margin-right: 8px;
        }

        .blurred {
            filter: blur(5px);
            color: transparent; /* Membuat teks menjadi transparan jika blur */
        }

        /* Add these styles to your CSS */
    #tableContainer {
        max-height: none;
        opacity: 1;
        visibility: visible;
        overflow: hidden;
    }

    /* Custom Toggle Switch Hover Effect */
    .peer:checked:hover ~ .peer-checked\:bg-blue-600 {
        background-color: #2563eb;
    }

    .peer:not(:checked):hover ~ .bg-gray-200 {
        background-color: #e5e7eb;
    }
</style>