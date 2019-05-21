---
Title: Journal Club - Building Scalable Apps with Aardvark.Media
Description: Step by Step walkthrough of Journal Club Presentation
Author: Thomas Ortner
Date: 2018-11-28
Robots: noindex,nofollow
Template: post
---
# Journal Club - Building Scalable Apps with Aardvark.Media

## Functional Programming

## Why use functional programming? Why use FSharp?

Scott Wlaschin: "Fsharp ... is an excellent choice for enterprise development. Here are five good reasons why you should consider using F# for your next project."

1. Conciseness
1. Convenience
1. Correctness
1. Concurrency
1. Completeness



We will pick some of Scott's examples to explain functional principles and the corresponding of F# notation. For details please refer to the link [https://fsharpforfunandprofit.com/why-use-fsharp/](https://fsharpforfunandprofit.com/why-use-fsharp/) or Scott's blog in general.

```javascript
//function
let timesTwo a = a * 2

//record type
type Person = {
  firstName : string
  lastName : string
}

//union type
type Employee =
  | Worker  of Person
  | Manager of list<Person>
```

