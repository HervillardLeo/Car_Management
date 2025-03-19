# Tool4Cars

## Fonctionnalités

- **Changement de client** via cookie
- **Chargement des voitures** spécifiques à un client
- **Gestion des garages** (Accès restreint)
- **Protection CSRF** automatique`
- **Templates dynamiques** basés sur le client actif

##  Installation

### **Cloner le projet**  
```sh
git clone https://github.com/HervillardLeo/Tool4Cars.git
cd Tool4Cars
```

### **Installer les dépendances**

```sh
composer install
npm install
```

### **Start the server**
```sh
symfony server:start
```
Or 

```sh
php -S 127.0.0.1:8000 -t public
```

## **Améliorations futures**

-Ajout d’une base de données avec les données clients
-Authentification utilisateur via JWT