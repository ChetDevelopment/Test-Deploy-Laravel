# 🚀 AWS Deployment Guide - Full Stack (Vue 3 + Laravel)

This guide covers deploying your complete application (Frontend + Backend) to AWS using Docker and CI/CD.

---

## 📋 Project Structure

```
├── frontend/          # Vue 3 + Vite SPA
│   ├── src/
│   ├── nginx.conf    # Nginx config for frontend
│   └── Dockerfile    # Frontend container
├── backend/           # Laravel 10 API
│   ├── app/
│   ├── database/
│   └── ...
├── nginx.conf         # Combined nginx (frontend + backend)
├── Dockerfile         # Full-stack container
├── docker-compose.yml # Local development
└── .github/workflows/deploy.yml
```

---

## 🏗️ AWS Infrastructure Setup

### 1. Create ECR Repositories (2 containers)

**Backend Repository:**
1. Go to **Amazon ECR → Repositories → Create repository**
2. Name: `backend-app`
3. Settings: Private, Tag immutability enabled

**Frontend Repository:**
1. Go to **Amazon ECR → Repositories → Create repository**
2. Name: `frontend-app`
3. Settings: Private, Tag immutability enabled

### 2. Create RDS Database (MySQL)

1. **RDS → Databases → Create database**
2. Engine: MySQL 8.0
3. DB instance: `laravel-db`
4. Credentials: Note username/password
5. Database name: `laravel`

### 3. Create ElastiCache (Redis)

1. **ElastiCache → Redis → Create**
2. Name: `laravel-redis`
3. Node type: cache.t3.micro (free tier eligible)

### 4. Create ECS Cluster

1. **ECS → Clusters → Create Cluster**
2. Name: `fullstack-cluster`
3. Infrastructure: AWS Fargate
4. VPC: Create new (2 public subnets)

### 5. Create Application Load Balancer

1. **EC2 → Load Balancers → Create**
2. Type: Application Load Balancer
3. Scheme: Internet-facing
4. Listeners: HTTP (80), HTTPS (443)
5. Target Groups:
   - `backend-tg` → Port 8080 (Backend)
   - `frontend-tg` → Port 80 (Frontend)

### 6. Create Secrets (Secrets Manager)

Store these secrets:
```
DB_HOST = your-rds-endpoint.rds.amazonaws.com
DB_DATABASE = laravel
DB_USERNAME = admin
DB_PASSWORD = your-password
REDIS_HOST = your-redis.cache.amazonaws.com
JWT_SECRET = (run: cd backend && php artisan jwt:secret)
APP_KEY = (from backend .env)
FRONTEND_URL = https://your-domain.com
```

---

## 🔧 GitHub Secrets Configuration

Add these to **Repository → Settings → Secrets and variables → Actions**:

| Secret | Description | Example |
|--------|-------------|---------|
| `AWS_ACCESS_KEY_ID` | IAM User Access Key | AKIAIOSFODNN7EXAMPLE |
| `AWS_SECRET_ACCESS_KEY` | IAM User Secret Key | wJalrXUtnFEMI/K7MDENG/... |
| `AWS_REGION` | AWS Region | us-east-1 |
| `BACKEND_ECR` | Backend ECR URL | 123456789012.dkr.ecr.us-east-1.amazonaws.com |
| `FRONTEND_ECR` | Frontend ECR URL | 123456789012.dkr.ecr.us-east-1.amazonaws.com |
| `BACKEND_TASK_DEFINITION` | Backend Task ARN | arn:aws:ecs:... |
| `FRONTEND_TASK_DEFINITION` | Frontend Task ARN | arn:aws:ecs:... |
| `BACKEND_CONTAINER_NAME` | Backend container | backend-app |
| `FRONTEND_CONTAINER_NAME` | Frontend container | frontend-app |
| `ECS_CLUSTER` | Cluster name | fullstack-cluster |
| `BACKEND_SERVICE_NAME` | Backend service | backend-service |
| `FRONTEND_SERVICE_NAME` | Frontend service | frontend-service |

---

## 📦 ECS Task Definitions

### Backend Task Definition (backend-task-def.json):
```json
{
  "family": "backend-app",
  "networkMode": "awsvpc",
  "requiresCompatibilities": ["FARGATE"],
  "cpu": "512",
  "memory": "1024",
  "executionRoleArn": "arn:aws:iam::ACCOUNT:role/ecsTaskExecutionRole",
  "containerDefinitions": [{
    "name": "backend-app",
    "image": "ACCOUNT.dkr.ecr.REGION.amazonaws.com/backend-app:latest",
    "essential": true,
    "portMappings": [{ "containerPort": 8080, "protocol": "tcp" }],
    "environment": [
      { "name": "APP_ENV", "value": "production" },
      { "name": "APP_DEBUG", "value": "false" }
    ],
    "secrets": [
      { "name": "DB_HOST", "valueFrom": "arn:aws:secretsmanager:REGION:ACCOUNT:secret:laravel/DB_HOST" },
      { "name": "DB_DATABASE", "valueFrom": "arn:aws:secretsmanager:REGION:ACCOUNT:secret:laravel/DB_DATABASE" },
      { "name": "DB_USERNAME", "valueFrom": "arn:aws:secretsmanager:REGION:ACCOUNT:secret:laravel/DB_USERNAME" },
      { "name": "DB_PASSWORD", "valueFrom": "arn:aws:secretsmanager:REGION:ACCOUNT:secret:laravel/DB_PASSWORD" },
      { "name": "JWT_SECRET", "valueFrom": "arn:aws:secretsmanager:REGION:ACCOUNT:secret:laravel/JWT_SECRET" }
    ],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "/ecs/backend-app",
        "awslogs-region": "REGION",
        "awslogs-stream-prefix": "ecs"
      }
    }
  }]
}
```

### Frontend Task Definition (frontend-task-def.json):
```json
{
  "family": "frontend-app",
  "networkMode": "awsvpc",
  "requiresCompatibilities": ["FARGATE"],
  "cpu": "256",
  "memory": "512",
  "containerDefinitions": [{
    "name": "frontend-app",
    "image": "ACCOUNT.dkr.ecr.REGION.amazonaws.com/frontend-app:latest",
    "essential": true,
    "portMappings": [{ "containerPort": 80, "protocol": "tcp" }],
    "logConfiguration": {
      "logDriver": "awslogs",
      "options": {
        "awslogs-group": "/ecs/frontend-app",
        "awslogs-region": "REGION",
        "awslogs-stream-prefix": "ecs"
      }
    }
  }]
}
```

---

## 🔄 CI/CD Pipeline Flow

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    PUSH     │───▶│    TEST     │───▶│    BUILD    │
│   TO MAIN   │    │ (PHP + Vue) │    │  (Docker)   │
└─────────────┘    └─────────────┘    └─────────────┘
                                             │
                   ┌─────────────┐           │
                   │   DEPLOY    │◀──────────┘
                   │   (ECS)     │
                   └─────────────┘
                        │
         ┌──────────────┼──────────────┐
         ▼              ▼              ▼
   ┌───────────┐  ┌───────────┐  ┌───────────┐
   │ FRONTEND │  │  BACKEND  │  │MIGRATIONS│
   │  (Vue)   │  │ (Laravel) │  │  (DB)    │
   └───────────┘  └───────────┘  └───────────┘
```

---

## 🧪 Local Development

### Build and Run:
```bash
# 1. Build frontend first
cd frontend
npm install
npm run build

# 2. Start all services
docker-compose up -d --build

# 3. View logs
docker-compose logs -f

# 4. Access:
# - Frontend: http://localhost
# - Backend API: http://localhost/api
# - MySQL: localhost:3306
# - Redis: localhost:6379
```

### Update Frontend API URL:
Edit `frontend/.env`:
```env
VITE_API_BASE_URL=/api
```

---

## 💰 Cost Estimation (us-east-1)

| Service | Estimate |
|---------|----------|
| ECS Fargate (Backend) | ~$15-25/month |
| ECS Fargate (Frontend) | ~$10-15/month |
| RDS MySQL (t3.micro) | ~$15/month |
| ElastiCache Redis | ~$15/month |
| Application Load Balancer | ~$16/month |
| ECR Storage | ~$2/month |
| CloudWatch Logs | ~$5/month |
| **Total** | **~$78-98/month** |

Free tier: ~$30-50/month (depending on usage)

---

## ✅ Deployment Checklist

- [ ] ECR repositories created (backend-app, frontend-app)
- [ ] RDS MySQL created and accessible
- [ ] ElastiCache Redis created
- [ ] ECS Cluster created
- [ ] ALB with target groups configured
- [ ] Secrets stored in Secrets Manager
- [ ] GitHub secrets configured
- [ ] Task definitions created
- [ ] ECS Services created
- [ ] Domain configured (optional)

---

## 🔧 Troubleshooting

### Check Logs:
```bash
# Backend logs
aws logs tail /ecs/backend-app --follow

# Frontend logs
aws logs tail /ecs/frontend-app --follow
```

### Common Issues:

| Issue | Solution |
|-------|----------|
| 502 Bad Gateway | Check ALB health checks |
| CORS errors | Configure CORS in Laravel |
| Database connection | Check security groups |
| Image pull fails | Verify ECR permissions |
| Frontend shows blank | Check API_URL environment |

---

## 📞 Quick Commands

```bash
# View running tasks
aws ecs list-tasks --cluster fullstack-cluster

# Force new deployment
aws ecs update-service --cluster fullstack-cluster --service backend-service --force-new-deployment
aws ecs update-service --cluster fullstack-cluster --service frontend-service --force-new-deployment

# Run migrations manually
aws ecs execute-command --cluster fullstack-cluster --task TASK_ID --container backend-app --command "php /app/backend/artisan migrate" --interactive

# Clear cache
aws ecs execute-command --cluster fullstack-cluster --task TASK_ID --container backend-app --command "php /app/backend/artisan optimize:clear" --interactive
```

---

For questions, check:
- [AWS ECS Docs](https://docs.aws.amazon.com/ecs/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Vue 3 Docs](https://vuejs.org/)
