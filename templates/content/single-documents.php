<section id="post-<?php the_ID(); ?>" <?php post_class('document single'); ?>>
   <?php
   $file_url = get_post_meta(get_the_ID(), '_document_file', true);
   $document_title = get_the_title();
   $file_name = basename($file_url);

   if ($file_url) :
   ?>

      <!-- Контейнер для прелоадера и прогресс-бара -->
      <div id="document-download-container" class="d-flex flex-column align-items-center justify-content-center min-vh-50 py-5">

         <!-- Прелоадер -->
         <div id="document-loader" class="text-center w-100">
            <div class="h4 text-body mb-4"><?php _e('Preparing document for download...', 'codeweber'); ?></div>

            <!-- Прогресс-бар Bootstrap -->
            <div class="progress w-100 mx-auto mb-3" style="max-width: 400px; height: 20px;">
               <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                  role="progressbar"
                  style="width: 0%; background-color: var(--bs-primary);"
                  aria-valuenow="0"
                  aria-valuemin="0"
                  aria-valuemax="100"></div>
            </div>

            <!-- Процент - крупный шрифт -->
            <div id="progress-text" class="display-6 fw-bold text-primary">0%</div>
         </div>

         <!-- Сообщение об ошибке -->
         <div id="download-error" class="d-none">
            <div class="text-center mt-4">
               <div class="text-danger mb-3">
                  <i class="uil uil-exclamation-triangle me-2"></i>
                  <?php _e('Document download error.', 'codeweber'); ?>
               </div>
               <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                  <a href="<?php echo esc_url($file_url); ?>" download="<?php echo esc_attr($file_name); ?>" class="btn btn-danger btn-md<?php echo getThemeButton(); ?>">
                     <i class="uil uil-import me-1"></i><?php _e('Download', 'codeweber'); ?>
                  </a>
                  <a href="<?php echo esc_url($file_url); ?>" target="_blank" class="btn btn-outline-danger btn-md<?php echo getThemeButton(); ?>">
                     <i class="uil uil-eye me-1"></i><?php _e('Open in Browser', 'codeweber'); ?>
                  </a>
               </div>
            </div>
         </div>
      </div>

      <script type="text/javascript">
         document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const loader = document.getElementById('document-loader');
            const errorMessage = document.getElementById('download-error');

            let progress = 0;
            const fileUrl = '<?php echo esc_url($file_url); ?>';
            const documentTitle = '<?php echo esc_js($document_title); ?>';
            const fileName = '<?php echo esc_js($file_name); ?>';

            // Translation strings
            const successMessage = '<?php echo esc_js(__('Document successfully downloaded!', 'codeweber')); ?>';
            const readyMessage = '<?php echo esc_js(__('Document ready for download!', 'codeweber')); ?>';
            const documentLabel = '<?php echo esc_js(__('Document', 'codeweber')); ?>';
            const autoDownloadFailed = '<?php echo esc_js(__('Automatic download failed. Choose an action:', 'codeweber')); ?>';
            const downloadDocumentText = '<?php echo esc_js(__('Download Document', 'codeweber')); ?>';
            const buttonStyle = '<?php echo esc_js(getThemeButton()); ?>';

            // Функция для обновления прогресса с округлением
            function updateProgress(value) {
               progress = Math.min(value, 100);
               const roundedProgress = Math.round(progress);

               progressBar.style.width = roundedProgress + '%';
               progressBar.setAttribute('aria-valuenow', roundedProgress);
               progressText.textContent = roundedProgress + '%';

               if (roundedProgress === 100) {
                  progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
                  progressBar.classList.add('bg-success');
                  progressText.classList.remove('text-primary');
                  progressText.classList.add('text-success');
               }
            }

            // Функция для принудительной загрузки файла
            function forceDownload() {
               fetch(fileUrl)
                  .then(response => response.blob())
                  .then(blob => {
                     const url = window.URL.createObjectURL(blob);
                     const link = document.createElement('a');
                     link.href = url;
                     link.download = fileName;
                     document.body.appendChild(link);
                     link.click();
                     document.body.removeChild(link);
                     window.URL.revokeObjectURL(url);
                  })
                  .catch(error => {
                     console.error('Download error:', error);
                     throw error;
                  });
            }

            // Альтернативный способ загрузки
            function alternativeDownload() {
               const link = document.createElement('a');
               link.href = fileUrl;
               link.download = fileName;
               link.style.display = 'none';
               document.body.appendChild(link);
               link.click();
               document.body.removeChild(link);
            }

            // Имитация прогресса загрузки
            function simulateProgress() {
               const interval = setInterval(function() {
                  progress += Math.random() * 15 + 5;

                  if (progress >= 90) {
                     clearInterval(interval);
                     updateProgress(90);
                     startActualDownload();
                  } else {
                     updateProgress(progress);
                  }
               }, 300);
            }

            // Фактическая загрузка файла
            function startActualDownload() {
               updateProgress(95);

               setTimeout(function() {
                  try {
                     // Пытаемся скачать файл
                     forceDownload();

                     updateProgress(100);

                     // Сообщение об успехе с двумя кнопками
                     setTimeout(function() {
                        loader.innerHTML = `
                            <div class="text-center">
                                <div class="h3 mb-3">${successMessage}</div>
                                <p class="h5 mb-3 text-muted"><strong>${documentTitle}</strong></p>
                                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-4">
                                    <a href="${fileUrl}" download="${fileName}" class="btn btn-primary btn-md${buttonStyle}">
                                        <i class="uil uil-import me-2"></i>${downloadDocumentText}
                                    </a>
                                </div>
                            </div>
                        `;
                     }, 1000);

                  } catch (e) {
                     // Если первый способ не сработал, пробуем альтернативный
                     try {
                        alternativeDownload();
                        updateProgress(100);

                        setTimeout(function() {
                           loader.innerHTML = `
                               <div class="text-center">
                                   <div class="h3 mb-3">${successMessage}</div>
                                   <p class="h5 mb-3 text-muted"><strong>${documentTitle}</strong></p>
                                   <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-4">
                                       <a href="${fileUrl}" download="${fileName}" class="btn btn-primary btn-md">
                                           <i class="uil uil-import me-2"></i>${downloadDocumentText}
                                       </a>
                                   </div>
                               </div>
                           `;
                        }, 500);

                     } catch (e2) {
                        // Если автоматическая загрузка не сработала
                        updateProgress(100);
                        setTimeout(function() {
                           loader.innerHTML = `
                               <div class="text-center">
                                   <div class="h3 mb-3">${readyMessage}</div>
                                   <p class="h5 mb-3 text-muted"><strong>${documentTitle}</strong></p>
                                   <div class="mb-4">
                                       <i class="uil uil-exclamation-triangle me-2"></i>
                                       ${autoDownloadFailed}
                                   </div>
                                   <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-4">
                                       <a href="${fileUrl}" download="${fileName}" class="btn btn-primary btn-md">
                                           <i class="uil uil-import me-2"></i>${downloadDocumentText}
                                       </a>
                                   </div>
                               </div>
                           `;
                        }, 500);
                     }
                  }
               }, 500);
            }

            function showError() {
               loader.classList.add('d-none');
               errorMessage.classList.remove('d-none');
            }

            // Запускаем процесс
            setTimeout(simulateProgress, 1000);

         });
      </script>

   <?php else : ?>
      <div class="text-center py-5 my-5">
         <div class="text-warning">
            <i class="uil uil-exclamation-triangle me-2"></i>
            <span class="h4"><?php _e('Document file not found.', 'codeweber'); ?></span>
         </div>
      </div>
   <?php endif; ?>
</section>

<style>
   .min-vh-50 {
      min-height: 50vh;
   }

   .progress {
      border-radius: 10px;
   }

   .progress-bar {
      border-radius: 10px;
      transition: width 0.3s ease;
   }

   @media (max-width: 576px) {
      .btn-md {
         padding: 0.75rem 1.5rem;
         font-size: 1rem;
      }
   }
</style>