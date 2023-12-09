terraform {
    required_providers {
        aws = {
            source  = "hashicorp/aws"
            version = "~> 4.16"
        }
    }

    required_version = ">= 1.2.0"

    backend "s3" {
        bucket = "terraform-state-awsdeveloper"
        key    = "terraform.tfstate"
        region = "eu-west-1"
        profile = "awsdeveloper"
    }
}

provider "aws" {
    region  = "eu-west-1"
    profile = "awsdeveloper"
}

resource "aws_security_group" "webserver_sg" {
    name = "webserver_sg"

    #Incoming traffic
    ingress {
        from_port = 443
        to_port = 443
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 80
        to_port = 80
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 22
        to_port = 22
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    ingress {
        from_port = 3000
        to_port = 3000
        protocol = "tcp"
        cidr_blocks = ["0.0.0.0/0"]
    }

    #Outgoing traffic
    egress {
        from_port = 0
        protocol = "-1"
        to_port = 0
        cidr_blocks = ["0.0.0.0/0"]
    }
}

#region EC2 Instance
resource "aws_instance" "app_server" {
    ami           = "ami-0be5a2a64756744f8"
    instance_type = "t4g.small"
    security_groups = [aws_security_group.webserver_sg.name]
    key_name = "awsdeveloper"

    tags = {
        Name = "webserver"
    }
}
#endregion

#region S3 Bucket
resource "aws_s3_bucket" "s3_awsdeveloper_invoices_bucket" {
    bucket = "awsdeveloper-invoices"
}

resource "aws_s3_bucket_ownership_controls" "s3_awsdeveloper_invoices_bucket_ownership_controls" {
    bucket = aws_s3_bucket.s3_awsdeveloper_invoices_bucket.id
    rule {
        object_ownership = "BucketOwnerPreferred"
    }
}

resource "aws_s3_bucket_public_access_block" "s3_awsdeveloper_invoices_bucket_public_access_block" {
    bucket = aws_s3_bucket.s3_awsdeveloper_invoices_bucket.id

    block_public_acls       = false
    block_public_policy     = false
    ignore_public_acls      = false
    restrict_public_buckets = false
}

resource "aws_s3_bucket_acl" "s3_awsdeveloper_invoices_bucket_acl" {
    depends_on = [
        aws_s3_bucket_ownership_controls.s3_awsdeveloper_invoices_bucket_ownership_controls,
        aws_s3_bucket_public_access_block.s3_awsdeveloper_invoices_bucket_public_access_block,
    ]

    bucket = aws_s3_bucket.s3_awsdeveloper_invoices_bucket.id
    acl = "public-read"
}
#endregion

#region DynamoDB
resource "aws_dynamodb_table" "dynamodb_awsdeveloper_invoice_items_table" {
    name = "awsdeveloper-invoice-items"
    billing_mode = "PROVISIONED"
    read_capacity = 5
    write_capacity = 5
    hash_key = "InvoiceId"
    range_key = "Title"

    attribute {
        name = "InvoiceId"
        type = "N"
    }

    attribute {
        name = "Title"
        type = "S"
    }
}
#endregion
