# AWS Deployment Guide for Laravel Application

This guide will walk you through deploying your Laravel application to AWS using Docker and CI/CD pipeline.

## 📋 Prerequisites

Before starting, ensure you have:
- [ ] AWS Account (create at https://aws.amazon.com)
- [ ] GitHub Repository with your Laravel code
- [ ] Domain name (optional, for custom domain)
- [ ] Basic knowledge of AWS Console

---

## 🏗️ Step 1: AWS Infrastructure Setup

### 1.1 Create IAM User for Deployment

1. Go to **IAM → Users → Create User**
2. User name: `github-deploy-user`
3. Select "Attach policies directly"
4. Add these policies:
   - `AmazonEC2ContainerRegistryFullAccess`
   - `AmazonECS_FullAccess`
   - `SecretsManagerReadWrite`
   - `CloudWatchLogsFullAccess`
5. Create user and save **Access Key ID** and **Secret Access Key**

### 1.2 Create ECR Repository (Container Registry)

1. Go to **Amazon ECR → Repositories → Create repository**
2. Repository name: `laravel-app`
3. Settings:
   - Visibility: Private
   - Tag immutability: Enabled
   - Scan on push: Enabled
4. Note your registry URL: `YOUR_ACCOUNT_ID.dkr.ecr.YOUR_REGION.amazonaws.com/laravel-app`

### 1.3 Create RDS Database (MySQL)

1. Go to **RDS → Databases → Create database**
2. Choose:
   - Engine: MySQL
   - Version: 8.0
   - Template: Free tier (or Production for production)
3. Settings:
   - DB instance identifier: `laravel-db`
   - Master username: `admin`
   - Master password: Create strong password
4. Additional settings:
   - Initial database name: `laravel`
5. Note all connection details for later

### 1.4 Create ElastiCache (Redis)

1. Go to **ElastiCache → Redis → Create**
2. Settings:
   - Name: `laravel-redis`
   - Node type: cache.t2.micro (or smaller for dev)
   - Number of replicas: 0 (for cost saving)
3. Note the Primary Endpoint

### 1.5 Create Secrets in Secrets Manager

1. Go to **Secrets Manager → Store a new secret**
2. Create secrets for:
   - `DB_HOST` → Your RDS endpoint
   - `DB_DATABASE` → laravel
   - `DB_USERNAME` → admin
   - `DB_PASSWORD` → Your DB password
   - `REDIS_HOST` → Your ElastiCache endpoint
   - `REDIS_PASSWORD` → (if enabled)
   - `JWT_SECRET` → Run `php artisan jwt:secret` locally and copy the result
   - `APP_KEY` → Your base64 APP_KEY from `.env`

### 1.6 Create ECS Cluster

1. Go to **ECS → Clusters → Create Cluster**
2. Choose **AWS Fargate** (serverless)
3. Settings:
   - Cluster name: `laravel-cluster`
   - VPC: Create new
   - Subnets: Select 2-3 availability zones
   - Enable Container Insights

### 1.7 Create Application Load Balancer

1. Go to **EC2 → Load Balancers → Create Load Balancer**
2. Choose **Application Load Balancer**
3. Settings:
   - Name: `laravel-alb`
   - Scheme: Internet-facing
   - IP address type: IPv4
4. Network:
   - VPC: Select your ECS VPC
   - Subnets: Select public subnets
5. Security: Create new security group (allow HTTP 80, HTTPS 443)
6. Target Group:
   - Name: `laravel-tg`
   - Protocol: HTTP
   - Port: 80
   - Health check path: `/`

### 1.8 Create ECS Task Definition

1. Go to **ECS → Task Definitions → Create new Task Definition**
2. Choose **Fargate**
3. Configure:
   - Task definition name: `laravel-app`
   - Task role: Create new (or use existing)
   - Network mode: `awsvpc`
   - Task memory: 1GB
   - Task CPU: 0.5 vCPU
4. Add Container:
   - Name: `laravel-app`
   - Image: Your ECR URI
   - Port mappings: 80
   - Environment: Add your environment variables
   - Secrets: Link to Secrets Manager
5. Create Task Definition

### 1.9 Create ECS Service

1. Go to your **ECS Cluster → Services → Create**
2. Configure:
   - Launch type: Fargate
   - Task definition: Select your task
   - Service name: `laravel-app`
   - Number of tasks: 1
3. Load Balancing:
   - Load balancer type: Application Load Balancer
   - Load balancer: Select your ALB
   - Container name:port: laravel-app:80
4. Service auto scaling: Optional (recommended for production)
5. Create Service

---

## 🔧 Step 2: Configure GitHub Secrets

1. Go to your **GitHub Repository → Settings → Secrets and variables → Actions**
2. Add these **Repository secrets**:

| Secret Name | Value | Example |
|-------------|-------|---------|
| `AWS_ACCESS_KEY_ID` | IAM User Access Key | AKIAIOSFODNN7EXAMPLE |
| `AWS_SECRET_ACCESS_KEY` | IAM User Secret Key | wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY |
| `AWS_REGION` | Your AWS Region | us-east-1 |
| `ECR_REGISTRY` | ECR Repository URL | 123456789012.dkr.ecr.us-east-1.amazonaws.com |
| `IMAGE_NAME` | Container name | laravel-app |
| `ECS_CLUSTER_NAME` | ECS Cluster | laravel-cluster |
| `ECS_SERVICE_NAME` | ECS Service | laravel-app |
| `ECS_TASK_DEFINITION` | Task Definition ARN | arn:aws:ecs:us-east-1:123456789012:task-definition/laravel-app |
| `ECS_CONTAINER_NAME` | Container name | laravel-app |

---

## 🚀 Step 3: First Deployment

### 3.1 Prepare Your Environment File

Create a production `.env.production` file:

```env
APP_NAME="Laravel App"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stderr
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=admin
DB_PASSWORD=your-password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=your-redis-endpoint.cache.amazonaws.com
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

JWT_SECRET=your-jwt-secret
```

### 3.2 Generate Required Keys

```bash
# Generate Application Key
php artisan key:generate --show

# Generate JWT Secret
php artisan jwt:secret --force
```

### 3.3 Run Database Migrations

After first deployment, run migrations via ECS execute command or create a startup script.

---

## 📊 Step 4: CI/CD Pipeline Overview

Your pipeline (`.github/workflows/deploy.yml`) will:

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   PUSH TO   │────▶│    TEST     │────▶│    BUILD    │────▶│   DEPLOY    │
│   MASTER    │     │   (PHPUnit) │     │   (Docker)  │     │   (ECS)     │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                          │                   │                   │
                     Run unit tests    Build & push to    Update ECS task
                     & verify code      ECR registry       & run migrations
```

### Pipeline Stages:

1. **Test Stage**: Runs PHPUnit tests
2. **Build Stage**: Builds Docker image and pushes to ECR
3. **Deploy Stage**: Updates ECS service with new image and runs migrations

---

## 🔍 Step 5: Monitoring & Troubleshooting

### 5.1 Check Logs

```bash
# CloudWatch Logs
# Go to CloudWatch → Log Groups → /ecs/laravel-app
```

### 5.2 ECS Execute Command (Debug)

```bash
aws ecs execute-command \
  --cluster laravel-cluster \
  --task YOUR_TASK_ID \
  --container laravel-app \
  --command "/bin/sh" \
  --interactive
```

### 5.3 Common Issues

| Issue | Solution |
|-------|----------|
| 502 Bad Gateway | Check ALB target group health |
| Database connection failed | Verify RDS security group allows ECS |
| Permission denied | Check IAM roles and secrets |
| Image pull failed | Verify ECR permissions |

---

## 💰 Estimated Monthly Costs (us-east-1)

| Service | Estimated Cost |
|---------|---------------|
| ECS (Fargate) | ~$15-25/month |
| RDS (MySQL t3.micro) | ~$15/month |
| ElastiCache (t3.micro) | ~$15/month |
| ALB | ~$16/month |
| ECR | ~$1/month |
| CloudWatch | ~$5/month |
| **Total** | **~$67-77/month** |

For development/free-tier eligible: ~$0-30/month

---

## 📝 Quick Commands Reference

```bash
# View running tasks
aws ecs list-tasks --cluster laravel-cluster

# View service status
aws ecs describe-services --cluster laravel-cluster --services laravel-app

# Update service (manual deploy)
aws ecs update-service --cluster laravel-cluster --service laravel-app --force-new-deployment

# View logs
aws logs tail /ecs/laravel-app --follow
```

---

## ✅ Deployment Checklist

Before deploying to production, ensure:

- [ ] All tests passing locally
- [ ] Environment variables configured in Secrets Manager
- [ ] Database migrations tested
- [ ] SSL certificate configured (ACM)
- [ ] Custom domain pointing to ALB
- [ ] Health check endpoint working
- [ ] Backup strategy in place
- [ ] Monitoring alerts configured
- [ ] Rollback plan documented

---

## 🔄 Rollback Procedure

If deployment fails:

1. Go to **ECS → Clusters → Your Cluster → Services**
2. Select your service
3. Click **Update Service**
4. Set "Force new deployment" to **unchecked**
5. Previous task will restart with old image
6. Or manually redeploy previous tagged image

---

For additional help, check:
- [AWS ECS Documentation](https://docs.aws.amazon.com/ecs/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
