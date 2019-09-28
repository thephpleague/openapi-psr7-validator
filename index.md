---
layout: default
permalink: /
title: Introduction
---

# Introduction

This package can validate PSR-7 messages against OpenAPI (3.0.x) specifications expressed in YAML or JSON.

## OpenAPI (OAS) Terms
There are some specific terms that are used in the package. These terms come 
from OpenAPI:
- `specification` - an OpenAPI document describing an API, expressed in JSON or YAML file
- `data` - actual thing that we validate against a specification, including body and metadata
- `schema` - the part of the specification that describes the body of the request / response
- `keyword` - properties that are used to describe the instance are called key
words, or schema keywords
- `path` - a relative path to an individual endpoint
- `operation` - a method that we apply on the path (like `get /password`)
- `response` - described response (includes status code, content types etc)
