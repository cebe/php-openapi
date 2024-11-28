### array merge recursive distinct

While resolving `allOf`s (pull request https://github.com/cebe/php-openapi/pull/208), if a duplicate property is found

```yaml
components:
  schemas:
    User:
      type: object
      required:
        - id
        - name # <--------------------------------------------------------------
      properties:
        id:
          type: integer
        name: # <--------------------------------------------------------------
          type: string
          maxLength: 10 # <--------------------------------------------------------------
    Pet:
      type: object
      required:
        - id2
        - name # <--------------------------------------------------------------
      properties:
        id2:
          type: integer
        name: # <--------------------------------------------------------------
          type: string
          maxLength: 12 # <--------------------------------------------------------------
    Post:
      type: object
      properties:
        id:
          type: integer
        content:
          type: string
        user:
          allOf:
            - $ref: '#/components/schemas/User'
            - $ref: '#/components/schemas/Pet'
            - x-faker: true
```

then property from the last component schema will be considered:

```yaml
Post:
  type: object
  properties:
    id:
      type: integer
    content:
      type: string
    user:
      type: object
      required:
        - id
        - name # <--------------------------------------------------------------
        - id2
      properties:
        id:
          type: integer
        name: # <--------------------------------------------------------------
          type: string
          maxLength: 12 # <--------------------------------------------------------------
        id2:
          type: integer
      x-faker: true
```