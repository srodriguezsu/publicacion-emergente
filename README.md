# Publicación Emergente

**Versión:** 1.4  
**Autor:** Sebastian Rodriguez  
**Descripción:**  
Este plugin permite mostrar una ventana emergente (popup) en la página de inicio de tu sitio WordPress, destacando una entrada seleccionada. Solo una entrada puede estar activa al mismo tiempo. El contenido del popup incluye el título, extracto y un botón de "Leer más".

---

## 🎯 Características

- Meta box en el editor de entradas: "Mostrar en la ventana emergente".
- Solo una entrada activa a la vez (la última marcada sobrescribe a las anteriores).
- Popup aparece automáticamente 2 segundos después de cargar la página de inicio.
- Personalizable desde el panel de administración:
  - Título del popup
  - Colores (primario, fondo, texto, título)
  - Borde redondeado
  - Frecuencia de aparición (cada X minutos)
- Bloquea scroll e interacción con el fondo mientras está visible.
- Compatible con dispositivos móviles.
- El popup se muestra solo una vez cada X minutos usando `localStorage`.

---

## ⚙️ Instalación

1. Copia la carpeta del plugin en:  
   `wp-content/plugins/publicacion-emergente/`
2. Activa el plugin desde el panel de **Plugins** en WordPress.
3. Dirígete a **Publicación Emergente** en el menú de administración para configurar.
4. Edita una entrada y marca **"Mostrar en la ventana emergente"** en la barra lateral.

---

## 🧪 Uso

- Ve a la sección **Entradas**.
- Edita la entrada que deseas mostrar en el popup.
- Marca la casilla **"Mostrar en la ventana emergente"** y actualiza.
- Esa entrada se mostrará automáticamente como popup en la página de inicio.

---

## 🖌 Personalización

Desde **Ajustes > Publicación Emergente** puedes modificar:

- Título del encabezado
- Color del encabezado y botón
- Color del texto y fondo
- Borde redondeado del popup
- Intervalo de reaparición (en minutos)

---

## 🛠 Requisitos

- WordPress 5.0 o superior
- Tema con soporte para `wp_footer()`

---

## 📌 Notas

- Solo se permite una entrada activa a la vez. Marcar una nueva entrada desactiva automáticamente la anterior.
- Si desmarcas la casilla y actualizas la entrada, el popup se desactiva.

