<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BlurDataForGuests
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya proses jika response berupa HTML dan user belum login
        if (!Auth::check() && $this->isHtmlResponse($response)) {
            $content = $response->getContent();

            // Tambahkan CSS dan JS untuk blur
            $blurStyles = '<style>
                .data-sensitive {
                    filter: blur(5px);
                    user-select: none;
                }
                .login-prompt {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background-color: white;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    z-index: 1000;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }
                .blur-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    backdrop-filter: blur(2px);
                    z-index: 999;
                }
            </style>';

            $loginPrompt = '<div class="blur-overlay"></div>
                <div class="login-prompt">
                    <h2>Data Tersensor</h2>
                    <p>Silakan login untuk melihat data lengkap</p>
                    <a href="/login" class="bg-blue-600 text-white px-4 py-2 rounded">Login</a>
                </div>';

            $script = '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Tambahkan class data-sensitive ke semua elemen yang perlu diblur
                    document.querySelectorAll(".tooltip-content, .chart-container, .card-data, .data-table tbody").forEach(function(el) {
                        el.classList.add("data-sensitive");
                    });
                    
                    // Tambahkan login prompt ke body
                    document.body.insertAdjacentHTML("beforeend", `' . $loginPrompt . '`);
                });
            </script>';

            // Sisipkan CSS dan JS sebelum </body>
            $content = str_replace('</body>', $blurStyles . $script . '</body>', $content);
            $response->setContent($content);
        }

        return $response;
    }

    private function isHtmlResponse($response): bool
    {
        return $response->headers->get('Content-Type') === 'text/html; charset=UTF-8';
    }
}
