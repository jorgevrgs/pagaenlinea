# README

Paga En Línea RBM is a payment application available in Colombia from Redeban Multicolor.

## Docker

Generate a .env file by copying and customizing the .env.example available on the repository:

```
mv .env.example .env
```

Run our docker-compose.yml to deploy a development instance to test your module:

```
docker-compose up
```

After the process is finished you'll be able to access a PrestaShop store in:

### Front Office

```
localhost:8080
```

### Back Office

```
localhost:8080/adminPS
```

adminPS is the folder that you set on the .env file in the var PS_FOLDER_ADMIN.

### Spanish

- Código Único de Ventas No Presenciales requerido.
- Conexión segura HTTPS requerida.
- Más información en http://pagaenlinearbm.com.co.

### English

- Single non-face-to-face sales code.
- Secure https connection required.
- More info in http://pagaenlinearbm.com.co.
